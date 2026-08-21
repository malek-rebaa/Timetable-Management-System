<?php

namespace App\Services\Timetable\Strategies;

use App\Services\Timetable\DTO\PlacedSession;
use App\Services\Timetable\DTO\SessionRequest;
use App\Services\Timetable\OccupancyRegistry;
use App\Services\Timetable\SlotGrid;

class GreedyPlacementStrategy
{
    protected ?string $lastError = null;

    public function __construct(
        protected OccupancyRegistry $registry,
        protected SlotGrid $grid,
    ) {
    }

    public function getLastError(): ?string
    {
        return $this->lastError;
    }

    /**
     * Tente de placer une demande de session sur le premier créneau disponible.
     */
    public function place(SessionRequest $request): ?PlacedSession
    {
        $this->lastError = null;
        $slotsNeeded = $request->durationSlots($this->grid->slotStep);
        
        if ($slotsNeeded <= 0) {
            $this->lastError = 'DURÉE INVALIDE';
            return null;
        }

        $days = $this->grid->days;
        
        // Raisons d'échec rencontrées
        $reasons = [
            'CLASS_CONFLICT' => 0,
            'NO_TEACHER' => 0,
            'NO_ROOM' => 0,
            'BREAK_CONFLICT' => 0,
        ];

        foreach ($days as $day) {
            $slotsForDay = $this->grid->slotsForDay($day);
            $maxSlotIndex = count($slotsForDay) - $slotsNeeded;

            for ($slotIndex = 0; $slotIndex <= $maxSlotIndex; $slotIndex++) {
                // Vérifier la classe
                if (!$this->registry->isClassFree($request->classRoom->id, $request->groupNumber, $day, $slotIndex, $slotsNeeded)) {
                    $reasons['CLASS_CONFLICT']++;
                    continue;
                }

                if (! $this->grid->isContiguous($day, $slotIndex, $slotsNeeded)) {
                    $reasons['BREAK_CONFLICT']++;
                    continue;
                }

                // Trouver l'enseignant libre avec la charge la plus faible
                $availableTeachers = [];
                foreach ($request->teacherIds as $tid) {
                    if ($this->registry->isTeacherFree($tid, $day, $slotIndex, $slotsNeeded)) {
                        $availableTeachers[] = $tid;
                    }
                }
                if (empty($availableTeachers)) {
                    $reasons['NO_TEACHER']++;
                    continue;
                }

                // Privilégier l'enseignant avec la charge horaire la plus faible
                usort($availableTeachers, fn ($a, $b) =>
                    $this->registry->teacherLoad($a) <=> $this->registry->teacherLoad($b)
                );
                $teacherId = $availableTeachers[0];

                // Trouver une salle libre (si requise)
                $roomId = null;
                if (!empty($request->roomIds)) {
                    foreach ($request->roomIds as $rid) {
                        if ($this->registry->isRoomFree($rid, $day, $slotIndex, $slotsNeeded)) {
                            $roomId = $rid;
                            break;
                        }
                    }
                    if ($roomId === null) {
                        $reasons['NO_ROOM']++;
                        continue;
                    }
                } else {
                    // Une séance du planning doit être adossée à une salle compatible.
                    $this->lastError = 'AUCUNE SALLE COMPATIBLE CONFIGURÉE';
                    return null;
                }

                // Tout est OK — réserver
                $this->registry->book(
                    $teacherId, 
                    $roomId, 
                    $request->classRoom->id, 
                    $request->groupNumber,
                    $day, 
                    $slotIndex, 
                    $slotsNeeded
                );

                $startTime = $this->grid->slotStart($day, $slotIndex);
                $endTimeIndex = $slotIndex + $slotsNeeded - 1;
                $endTime = $this->grid->slotEnd($day, $endTimeIndex);

                return new PlacedSession(
                    subjectPlanId: $request->subjectPlan->id,
                    teacherId: $teacherId,
                    classRoomId: $request->classRoom->id,
                    roomId: $roomId,
                    day: $day,
                    startTime: $startTime,
                    endTime: $endTime,
                    groupNumber: $request->groupNumber,
                );
            }
        }

        // Analyser les raisons de l'échec pour donner le meilleur feedback
        if ($reasons['CLASS_CONFLICT'] > 0 && array_sum($reasons) == $reasons['CLASS_CONFLICT']) {
            $this->lastError = 'CONFLIT CLASSE (La classe est occupée sur tous les créneaux possibles)';
        } elseif ($reasons['NO_TEACHER'] > 0 && $reasons['NO_ROOM'] == 0) {
            $this->lastError = 'CONFLIT ENSEIGNANT (Aucun enseignant disponible)';
        } elseif ($reasons['NO_ROOM'] > 0) {
            $this->lastError = 'CONFLIT SALLE (Aucune salle disponible)';
        } else {
            $this->lastError = 'GRILLE PLEINE (Chevauchements multiples)';
        }

        return null;
    }
}
