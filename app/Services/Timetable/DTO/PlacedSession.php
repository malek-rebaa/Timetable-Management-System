<?php

namespace App\Services\Timetable\DTO;

/**
 * DTO immuable représentant une séance qui a été placée avec succès
 * par l'algorithme, prête à être persistée.
 */
class PlacedSession
{
    public function __construct(
        public int $subjectPlanId,
        public int $teacherId,
        public int $classRoomId,
        public ?int $roomId,
        public string $day,
        public string $startTime,
        public string $endTime,
        public ?int $groupNumber,
    ) {
    }
}