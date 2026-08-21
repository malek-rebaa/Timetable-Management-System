<?php

namespace App\Services\Timetable;

use App\Models\AcademicSession;
use App\Models\ClassRoom;
use App\Models\Room;
use App\Models\SubjectPlan;
use App\Models\Timetable;
use App\Models\User;
use App\Services\Timetable\DTO\PlacedSession;
use App\Services\Timetable\Strategies\GreedyPlacementStrategy;

/**
 * Orchestrateur de génération d'emploi du temps.
 *
 * Flux :
 * 1. RequestBuilder -> construire les demandes de placement
 * 2. Tri par difficulté (MRV)
 * 3. GreedyPlacementStrategy -> tenter de placer chaque séance
 * 4. Validation globale
 * 5. TimetableCommitService -> persister le résultat
 */
class TimetableGenerator
{
    public function __construct(
        protected RequestBuilder $requestBuilder,
        protected OccupancyRegistry $registry,
        protected SlotGrid $grid,
        protected TimetableCommitService $commitService,
        protected ConflictChecker $conflictChecker,
    ) {
    }

    /**
     * Génère l'emploi du temps pour un Timetable donné.
     */
    public function generate(Timetable $timetable, array $options = []): array
    {
        $options['timetable_id'] = $timetable->id;

        $diagnostics = $this->buildDiagnostics($options);

        $requests = $this->requestBuilder->build($options);
        $diagnostics['sessions_requested'] = $this->countRequestedSessions($requests);

        if (empty($requests)) {
            $this->commitService->markFailed($timetable);

            return [
                'success' => false,
                'placed' => 0,
                'unplaced' => 0,
                'errors' => ['Aucune demande de séance à générer.'],
                'diagnostics' => $diagnostics,
            ];
        }

        foreach ($requests as $request) {
            $request->computeDifficulty($this->grid->slotStep);
        }
        usort($requests, fn ($a, $b) => $b->difficultyScore <=> $a->difficultyScore);

        $placed = [];
        $errors = [];
        $maxAttempts = (int) config('timetable.max_attempts', 10);

        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            $this->registry = new OccupancyRegistry($this->grid);
            $strategy = new GreedyPlacementStrategy($this->registry, $this->grid);

            // Load locked sessions into the NEW registry instance
            $this->loadLockedSessions($timetable);

            $attemptRequests = $attempt > 0 ? $this->shuffleRequests($requests) : $requests;

            $currentPlaced = [];
            $currentErrors = [];

            foreach ($attemptRequests as $request) {
                for ($i = 0; $i < $request->sessionsCount; $i++) {
                    $result = $strategy->place($request);
                    if ($result) {
                        $currentPlaced[] = $result;
                    } else {
                        $reason = $strategy->getLastError() ?? 'Erreur inconnue';
                        $currentErrors[] = $this->buildErrorMessage($request) . " (Séance " . ($i + 1) . "/" . $request->sessionsCount . ") - Raison : " . $reason;
                    }
                }
            }

            if ($attempt === 0 || count($currentPlaced) > count($placed)) {
                $placed = $currentPlaced;
                $errors = $currentErrors;
            }

            if (empty($errors)) {
                break;
            }
        }

        $diagnostics['sessions_placed'] = count($placed);
        $diagnostics['sessions_unplaced'] = count($errors);
        $diagnostics['teachers_used'] = count(array_unique(array_column($placed, 'teacherId')));
        $diagnostics['rooms_used'] = count(array_unique(array_filter(array_column($placed, 'roomId'))));

        // CRITICAL: Success requires ALL requested sessions to be placed with NO errors
        $success = empty($errors) && count($placed) === $diagnostics['sessions_requested'];

        if ($success) {
            $validationErrors = $this->validatePlacement($placed, $requests);
            if (! empty($validationErrors)) {
                $errors = array_merge($errors, $validationErrors);
                $success = false;
            }
        }

        if ($success) {
            try {
                $this->commitService->commit(
                    timetable: $timetable,
                    placedSessions: $placed
                );
            } catch (\Throwable $e) {
                $errors[] = 'Erreur de persistance: '.$e->getMessage();
                $success = false;
            }
        }

        if (! $success) {
            $this->commitService->markFailed($timetable);
        }

        $diagnostics['conflicts'] = $success ? ['class' => 0, 'teacher' => 0, 'room' => 0] : null;

        return [
            'success' => $success,
            'placed' => count($placed),
            'unplaced' => count($errors),
            'errors' => $errors,
            'diagnostics' => $diagnostics,
        ];
    }

    /**
     * Valide l'ensemble des séances placées avant persistance.
     *
     * @param  PlacedSession[]  $placed
     * @param  \App\Services\Timetable\DTO\SessionRequest[]  $requests
     * @return string[]
     */
    protected function validatePlacement(array $placed, array $requests): array
    {
        $errors = [];

        $expectedCount = $this->countRequestedSessions($requests);
        if (count($placed) !== $expectedCount) {
            $errors[] = sprintf(
                'Nombre de séances incorrect : %d demandées, %d placées.',
                $expectedCount,
                count($placed)
            );
        }

        for ($i = 0; $i < count($placed); $i++) {
            for ($j = $i + 1; $j < count($placed); $j++) {
                $a = $placed[$i];
                $b = $placed[$j];

                if ($a->day !== $b->day) {
                    continue;
                }

                $overlap = $a->startTime < $b->endTime && $a->endTime > $b->startTime;
                if (! $overlap) {
                    continue;
                }

                if ($a->classRoomId === $b->classRoomId) {
                    $errors[] = "Conflit classe détecté le {$a->day} entre {$a->startTime} et {$b->startTime}.";
                }
                if ($a->teacherId === $b->teacherId) {
                    $errors[] = "Conflit enseignant #{$a->teacherId} le {$a->day}.";
                }
                if ($a->roomId && $a->roomId === $b->roomId) {
                    $errors[] = "Conflit salle #{$a->roomId} le {$a->day}.";
                }
            }
        }

        foreach ($placed as $ps) {
            $plan = SubjectPlan::find($ps->subjectPlanId);
            if (! $plan) {
                continue;
            }

            $eligible = User::query()
                ->whereKey($ps->teacherId)
                ->where('role', 'TEACHER')
                ->where('is_active', true)
                ->whereHas('subjects', fn ($query) => $query->whereKey($plan->subject_id))
                ->exists();

            if (! $eligible) {
                $errors[] = "Enseignant #{$ps->teacherId} non habilité pour le plan #{$ps->subjectPlanId}.";
            }

            // Vérifier la salle
            if ($ps->roomId) {
                $room = Room::find($ps->roomId);
                $class = ClassRoom::find($ps->classRoomId);
                
                if ($room && $class) {
                    // Type compatible ?
                    $allowedTypes = config("timetable.room_types.{$plan->teaching_type}", []);
                    if (!in_array($room->type, $allowedTypes, true)) {
                        $errors[] = "Salle #{$room->id} ({$room->type}) incompatible avec {$plan->teaching_type}.";
                    }

                    // Capacité suffisante ?
                    $tpGroups = (int) config('timetable.tp_groups', 2);
                    $minCapacity = $plan->teaching_type === 'TP' 
                        ? (int) ceil($class->student_count / $tpGroups) 
                        : $class->student_count;

                    if ($room->capacity < $minCapacity) {
                        $errors[] = "Capacité de la salle #{$room->id} ({$room->capacity}) insuffisante pour {$minCapacity} étudiants.";
                    }
                }
            }

            // Vérifier les créneaux (durée et continuité)
            $start = \Carbon\Carbon::parse($ps->startTime);
            $end = \Carbon\Carbon::parse($ps->endTime);
            $durationMinutes = $start->diffInMinutes($end, true);
            
            $startIndex = $this->grid->indexOf($ps->day, $ps->startTime);
            $slotsNeeded = $this->grid->durationToSlots($durationMinutes);
            
            if ($startIndex === null || !$this->grid->isContiguous($ps->day, $startIndex, $slotsNeeded)) {
                $errors[] = "Créneaux invalides ou traversant une pause le {$ps->day} de {$ps->startTime} à {$ps->endTime}.";
            }
        }

        return $errors;
    }

    /**
     * @param  \App\Services\Timetable\DTO\SessionRequest[]  $requests
     */
    protected function countRequestedSessions(array $requests): int
    {
        $total = 0;
        foreach ($requests as $r) {
            $total += $r->sessionsCount;
        }

        return $total;
    }

    protected function buildDiagnostics(array $options): array
    {
        $classQuery = ClassRoom::query();
        if (isset($options['class_room_ids'])) {
            $classQuery->whereIn('id', (array) $options['class_room_ids']);
        }

        return [
            'classes_analyzed' => $classQuery->count(),
            'plans_analyzed' => SubjectPlan::count(),
            'teachers_available' => User::where('role', 'TEACHER')->count(),
            'rooms_available' => Room::count(),
            'slots_per_day' => $this->grid->slotsPerDay,
            'working_days' => count($this->grid->days),
            'sessions_requested' => 0,
            'sessions_placed' => 0,
            'sessions_unplaced' => 0,
            'teachers_used' => 0,
            'rooms_used' => 0,
        ];
    }

    /**
     * Mélange l'ordre des requêtes pour diversifier les tentatives.
     *
     * @param  \App\Services\Timetable\DTO\SessionRequest[]  $requests
     * @return \App\Services\Timetable\DTO\SessionRequest[]
     */
    protected function shuffleRequests(array $requests): array
    {
        $groups = [];
        foreach ($requests as $r) {
            $score = $r->difficultyScore;
            $groups[$score][] = $r;
        }

        krsort($groups);
        $shuffled = [];
        foreach ($groups as $group) {
            shuffle($group);
            $shuffled = array_merge($shuffled, $group);
        }

        return $shuffled;
    }

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
            $durationMinutes = $start->diffInMinutes($end, true);
            $slots = $this->grid->durationToSlots($durationMinutes);

            $this->registry->book(
                (int) $session->teacher_id,
                (int) $session->room_id,
                (int) $session->class_room_id,
                $session->group_number,
                $session->day,
                $startIndex,
                $slots
            );
        }
    }

    protected function buildErrorMessage($request): string
    {
        $subjectName = $request->subjectPlan->subject->name ?? 'Matière #'.$request->subjectPlan->id;
        $className = $request->classRoom->name;
        $group = $request->groupNumber ? " Groupe {$request->groupNumber}" : '';

        return sprintf(
            'Impossible de placer : %s (%s) pour %s%s',
            $subjectName,
            $request->subjectPlan->teaching_type,
            $className,
            $group
        );
    }
}
