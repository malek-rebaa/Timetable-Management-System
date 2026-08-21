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
    public function build(array $options = []): array
    {
        $plans = SubjectPlan::with('subject')->get();
        $classes = ClassRoom::all();
        $rooms = Room::all();

        $classRoomIds = $options['class_room_ids'] ?? null;
        if ($classRoomIds !== null) {
            $classes = $classes->whereIn('id', (array) $classRoomIds)->values();
        }

        $timetableId = $options['timetable_id'] ?? null;
        $lockedCounts = $this->countLockedSessions($timetableId);

        $requests = [];

        foreach ($plans as $plan) {
            foreach ($classes as $class) {
                if ($plan->level_id !== $class->level_id) {
                    continue;
                }

                $teacherIds = $plan->teachers()->pluck('users.id')->toArray();

                $tpGroups = (int) config('timetable.tp_groups', 2);
                $minCapacity = $plan->teaching_type === 'TP'
                    ? (int) ceil($class->student_count / $tpGroups)
                    : $class->student_count;

                $roomIds = $this->filterCompatibleRooms($rooms, $plan->teaching_type, $minCapacity);

                if ($plan->teaching_type === 'TP') {
                    for ($group = 1; $group <= $tpGroups; $group++) {
                        $lockedKey = "{$class->id}_{$plan->id}_{$group}";
                        $alreadyPlaced = $lockedCounts[$lockedKey] ?? 0;
                        $remaining = max(0, $plan->sessions_per_week - $alreadyPlaced);

                        if ($remaining === 0) {
                            continue;
                        }

                        $requests[] = new SessionRequest(
                            id: 0,
                            levelId: $plan->level_id,
                            classRoom: $class,
                            subjectPlan: $plan,
                            groupNumber: $group,
                            sessionsCount: $remaining,
                            minCapacity: $minCapacity,
                            teacherIds: $teacherIds,
                            roomIds: $roomIds,
                        );
                    }
                } else {
                    $lockedKey = "{$class->id}_{$plan->id}_";
                    $alreadyPlaced = $lockedCounts[$lockedKey] ?? 0;
                    $remaining = max(0, $plan->sessions_per_week - $alreadyPlaced);

                    if ($remaining === 0) {
                        continue;
                    }

                    $requests[] = new SessionRequest(
                        id: 0,
                        levelId: $plan->level_id,
                        classRoom: $class,
                        subjectPlan: $plan,
                        groupNumber: null,
                        sessionsCount: $remaining,
                        minCapacity: $minCapacity,
                        teacherIds: $teacherIds,
                        roomIds: $roomIds,
                    );
                }
            }
        }

        return $requests;
    }

    /**
     * Compte les séances verrouillées par (classe, plan, groupe).
     *
     * @return array<string, int>
     */
    protected function countLockedSessions(?int $timetableId): array
    {
        if ($timetableId === null) {
            return [];
        }

        $counts = [];
        $locked = \App\Models\AcademicSession::where('timetable_id', $timetableId)
            ->where('is_locked', true)
            ->get(['class_room_id', 'subject_plan_id', 'group_number']);

        foreach ($locked as $session) {
            $group = $session->group_number ?? '';
            $key = "{$session->class_room_id}_{$session->subject_plan_id}_{$group}";
            $counts[$key] = ($counts[$key] ?? 0) + 1;
        }

        return $counts;
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