<?php

namespace App\Services\Timetable;

use App\Models\AcademicSession;
use App\Models\Room;
use App\Models\User;
use App\Services\Timetable\Contracts\ConstraintCheckerInterface;

/**
 * Validateur de conflits utilisé par le générateur.
 * Il utilise l'OccupancyRegistry en mémoire pour éviter les requêtes SQL (N+1).
 */
class MemoryConflictChecker implements ConstraintCheckerInterface
{
    public function __construct(
        protected OccupancyRegistry $registry,
        protected SlotGrid $grid
    ) {
    }

    public function check(AcademicSession $candidate, ?int $ignoreSessionId = null): array
    {
        $errors = [];

        $errors = array_merge($errors, $this->checkTeacher($candidate));
        $errors = array_merge($errors, $this->checkRoom($candidate));
        $errors = array_merge($errors, $this->checkOverlaps($candidate, $ignoreSessionId));
        $errors = array_merge($errors, $this->checkGroupConsistency($candidate));

        return $errors;
    }

    public function checkTeacher(AcademicSession $candidate): array
    {
        // En génération, la vérification de l'habilitation est faite en amont lors de la création
        // des requêtes (RequestBuilder). L'enseignant passé est supposé habilité.
        return [];
    }

    public function checkRoom(AcademicSession $candidate): array
    {
        // Pareil, le générateur ne tente de placer que dans des salles compatibles.
        return [];
    }

    public function checkOverlaps(AcademicSession $candidate, ?int $ignoreSessionId = null): array
    {
        $errors = [];
        $day = $candidate->day;
        
        $startIndex = $this->grid->indexOf($day, $candidate->start_time);
        
        // Si l'index est introuvable, c'est hors plage ou pause.
        if ($startIndex === null) {
            return ["Créneau horaire invalide."];
        }

        // Calcul du nombre de slots (on suppose que duration est correcte, 
        // ou on la recalcule si on a la fin. Le plus sûr est de regarder start_time et end_time)
        $start = \Carbon\Carbon::parse($candidate->start_time);
        $end = \Carbon\Carbon::parse($candidate->end_time);
        $durationMinutes = $end->diffInMinutes($start);
        
        $slots = $this->grid->durationToSlots($durationMinutes);

        if ($candidate->teacher_id && !$this->registry->isTeacherFree($candidate->teacher_id, $day, $startIndex, $slots)) {
            $errors[] = "L'enseignant a déjà une séance sur ce créneau.";
        }

        if ($candidate->room_id && !$this->registry->isRoomFree($candidate->room_id, $day, $startIndex, $slots)) {
            $errors[] = 'La salle est déjà occupée sur ce créneau.';
        }

        if ($candidate->class_room_id && ! $this->registry->isClassFree($candidate->class_room_id, $candidate->group_number, $day, $startIndex, $slots)) {
            $errors[] = 'La classe a déjà une séance sur ce créneau.';
        }

        return $errors;
    }

    public function checkGroupConsistency(AcademicSession $candidate): array
    {
        // La cohérence du groupe est garantie par le RequestBuilder.
        return [];
    }
}
