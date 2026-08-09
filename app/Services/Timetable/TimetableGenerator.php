<?php

namespace App\Services\Timetable;

use App\Models\Timetable;
use App\Services\Timetable\DTO\PlacedSession;

/**
 * Orchestrateur de génération d'emploi du temps.
 *
 * Flux :
 * 1. RequestBuilder -> construire les demandes de placement
 * 2. Tri par difficulté (MRV)
 * 3. GreedyPlacementStrategy -> tenter de placer chaque séance
 * 4. TimetableCommitService -> persister le résultat
 *
 * Retourne un tableau :
 * [
 *     'success' => bool,
 *     'placed' => int,          // nombre de séances placées
 *     'unplaced' => int,        // nombre de séances non placées
 *     'errors' => string[],     // erreurs détaillées
 * ]
 */
class TimetableGenerator
{
    public function __construct(
        protected RequestBuilder $requestBuilder,
        protected OccupancyRegistry $registry,
        protected SlotGrid $grid,
        protected TimetableCommitService $commitService,
    ) {
    }

    /**
     * Génère l'emploi du temps pour un Timetable donné.
     */
    public function generate(Timetable $timetable, array $options = []): array
    {
        // 1. Nettoyer les séances non verrouillées existantes
        $this->commitService->clearTimetable($timetable);

        // 2. Construire les demandes
        $requests = $this->requestBuilder->build();

        if (empty($requests)) {
            return [
                'success' => true,
                'placed' => 0,
                'unplaced' => 0,
                'errors' => [],
            ];
        }

        // 3. Trier par difficulté (MRV - Most Constrained Variable)
        foreach ($requests as $request) {
            $request->computeDifficulty($this->grid->slotStep);
        }
        usort($requests, fn ($a, $b) => $b->difficultyScore <=> $a->difficultyScore);

        // 4. Tenter de placer chaque séance
        $placed = [];
        $errors = [];

        $maxAttempts = (int) config('timetable.max_attempts', 10);

        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            // Réinitialiser le registre à chaque tentative
            $this->registry = new OccupancyRegistry($this->grid);
            $strategy = new \App\Services\Timetable\Strategies\GreedyPlacementStrategy($this->registry, $this->grid);

            // TODO: Ajouter les séances verrouillées au registre
            $this->loadLockedSessions($timetable);

            $currentPlaced = [];
            $currentErrors = [];

            foreach ($requests as $request) {
                $result = $strategy->place($request);
                if ($result) {
                    $currentPlaced[] = $result;
                } else {
                    $currentErrors[] = $this->buildErrorMessage($request);
                }
            }

            // Si meilleur résultat, on le garde
            if (count($currentPlaced) > count($placed)) {
                $placed = $currentPlaced;
                $errors = $currentErrors;
            }

            // Si tout est placé, on arrête
            if (empty($errors)) {
                break;
            }
        }

        // 5. Persister le résultat
        if (!empty($placed)) {
            try {
                $this->commitService->commit(
                    name: $timetable->name,
                    academicYear: $timetable->academic_year,
                    semester: $timetable->semester,
                    placedSessions: $placed
                );
            } catch (\Throwable $e) {
                $errors[] = 'Erreur de persistance: ' . $e->getMessage();
            }
        }

        $success = empty($errors);
        if (!$success) {
            $this->commitService->markFailed($timetable);
        }

        return [
            'success' => $success,
            'placed' => count($placed),
            'unplaced' => count($errors),
            'errors' => $errors,
        ];
    }

    /**
     * Charge les séances verrouillées dans le registre pour éviter de les écraser.
     */
    protected function loadLockedSessions(Timetable $timetable): void
    {
        $lockedSessions = $timetable->academicSessions()->where('is_locked', true)->get();

        foreach ($lockedSessions as $session) {
            $startIndex = $this->grid->indexOf($session->day, $session->start_time);
            if ($startIndex === null) {
                continue;
            }

            $start = \Carbon\Carbon::parse($session->start_time);
            $end = \Carbon\Carbon::parse($session->end_time);
            $durationMinutes = $end->diffInMinutes($start);
            $slots = $this->grid->durationToSlots($durationMinutes);

            $this->registry->book(
                $session->teacher_id ?? 0,
                $session->room_id ?? 0,
                $session->class_room_id ?? 0,
                $session->day,
                $startIndex,
                $slots
            );
        }
    }

    /**
     * Construit un message d'erreur lisible pour une session non placée.
     */
    protected function buildErrorMessage($request): string
    {
        $subjectName = $request->subjectPlan->subject->name ?? 'Matière #'.$request->subjectPlan->id;
        $className = $request->classRoom->name;
        $group = $request->groupNumber ? " Groupe {$request->groupNumber}" : '';

        $reasons = [];
        if (empty($request->teacherIds)) {
            $reasons[] = 'Aucun enseignant habilité';
        }
        if (empty($request->roomIds)) {
            $reasons[] = 'Aucune salle compatible disponible';
        }
        if (empty($reasons)) {
            $reasons[] = 'Aucun créneau libre disponible';
        }

        return sprintf(
            'Impossible de placer : %s (%s) pour %s%s — %s',
            $subjectName,
            $request->subjectPlan->teaching_type,
            $className,
            $group,
            implode(', ', $reasons)
        );
    }
}