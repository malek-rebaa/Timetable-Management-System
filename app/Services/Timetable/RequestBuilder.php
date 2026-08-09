<?php

namespace App\Services\Timetable;

use App\Models\ClassRoom;
use App\Models\Room;
use App\Models\SubjectPlan;
use App\Services\Timetable\DTO\SessionRequest;
use Illuminate\Support\Collection;

/**
 * Construit les demandes de placement à partir des données existantes.
 *
 * Pour chaque couple (classe, plan de matière) :
 * - filtre les enseignants habilités ;
 * - filtre les salles compatibles (type + capacité) ;
 * - calcule un score de difficulté pour un tri MRV.
 */
class RequestBuilder
{
    public function __construct(
        protected ConflictChecker $checker
    ) {
    }

    /**
     * Construit la liste des SessionRequest pour toutes les classes et tous les plans.
     *
     * @return SessionRequest[]
     */
    public function build(): array
    {
        $plans = SubjectPlan::with('subject')->get();
        $classes = ClassRoom::all();
        $rooms = Room::all();

        $requests = [];

        foreach ($plans as $plan) {
            foreach ($classes as $class) {
                // Vérifie que le plan correspond au niveau de la classe
                if ($plan->level_id !== $class->level_id) {
                    continue;
                }

                // Récupère les enseignants habilités pour cette matière
                $teacherIds = $plan->teachers()->pluck('id')->toArray();

                if (empty($teacherIds)) {
                    continue;
                }

                // Filtre les salles compatibles
                $roomIds = $this->filterCompatibleRooms($rooms, $plan->teaching_type, $class->student_count);

                // Détermine les groupes
                if ($plan->teaching_type === 'TP') {
                    $tpGroups = (int) config('timetable.tp_groups', 2);
                    for ($group = 1; $group <= $tpGroups; $group++) {
                        $request = new SessionRequest(
                            id: 0,
                            levelId: $plan->level_id,
                            classRoom: $class,
                            subjectPlan: $plan,
                            groupNumber: $group,
                            sessionsCount: $plan->sessions_per_week,
                            minCapacity: $class->student_count,
                            teacherIds: $teacherIds,
                            roomIds: $roomIds,
                        );
                        $requests[] = $request;
                    }
                } else {
                    // THEORY : pas de groupe (classe entière)
                    $request = new SessionRequest(
                        id: 0,
                        levelId: $plan->level_id,
                        classRoom: $class,
                        subjectPlan: $plan,
                        groupNumber: null,
                        sessionsCount: $plan->sessions_per_week,
                        minCapacity: $class->student_count,
                        teacherIds: $teacherIds,
                        roomIds: $roomIds,
                    );
                    $requests[] = $request;
                }
            }
        }

        return $requests;
    }

    /**
     * Filtre les salles compatibles pour un type d'enseignement et une capacité minimale.
     *
     * @return array<int, int> liste des room_id
     */
    protected function filterCompatibleRooms(Collection $rooms, string $teachingType, int $minCapacity): array
    {
        $allowedTypes = config("timetable.room_types.{$teachingType}", []);

        return $rooms->filter(function (Room $room) use ($allowedTypes, $minCapacity) {
            return in_array($room->type, $allowedTypes, true)
                && $room->capacity >= $minCapacity;
        })->pluck('id')->toArray();
    }
}