<?php

namespace App\Services\Timetable\Strategies;

use App\Services\Timetable\DTO\PlacedSession;
use App\Services\Timetable\DTO\SessionRequest;
use App\Services\Timetable\OccupancyRegistry;
use App\Services\Timetable\SlotGrid;

class GreedyPlacementStrategy
{
    public function __construct(
        protected OccupancyRegistry $registry,
        protected SlotGrid $grid
    ) {
    }

    /**
     * Tente de placer une demande de session sur le premier créneau disponible.
     */
    public function place(SessionRequest $request): ?PlacedSession
    {
        $slotsNeeded = $request->durationSlots($this->grid->slotStep);
        $days = $this->grid->days;

        foreach ($days as $day) {
            $slotsForDay = $this->grid->slotsForDay($day);
            $maxSlotIndex = count($slotsForDay) - $slotsNeeded;

            for ($slotIndex = 0; $slotIndex <= $maxSlotIndex; $slotIndex++) {
                // Vérifier la classe
                if (!$this->registry->isClassFree($request->classRoom->id, $day, $slotIndex, $slotsNeeded)) {
                    continue;
                }

                // Trouver un enseignant libre
                $teacherId = null;
                foreach ($request->teacherIds as $tid) {
                    if ($this->registry->isTeacherFree($tid, $day, $slotIndex, $slotsNeeded)) {
                        $teacherId = $tid;
                        break;
                    }
                }
                if ($teacherId === null) {
                    continue;
                }

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
                        continue;
                    }
                } else {
                    // Si aucune salle n'est requise (ex. cours en extérieur ou non précisé)
                    // (Le RequestBuilder assure que s'il faut une salle, la liste n'est pas vide)
                    $roomId = null;
                }

                // Tout est OK — réserver
                // S'il n'y a pas de salle, on passe 0 ou null. OccupancyRegistry gère cela si on modifie son comportement,
                // mais roomId est un int. Si null, on peut passer 0.
                $this->registry->book(
                    $teacherId, 
                    $roomId ?? 0, 
                    $request->classRoom->id, 
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

        return null;
    }
}
