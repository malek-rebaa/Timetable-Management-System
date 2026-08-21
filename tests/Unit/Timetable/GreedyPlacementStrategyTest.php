<?php

namespace Tests\Unit\Timetable;

use App\Models\ClassRoom;
use App\Models\Level;
use App\Models\Room;
use App\Models\Subject;
use App\Models\SubjectPlan;
use App\Models\User;
use App\Services\Timetable\ConflictChecker;
use App\Services\Timetable\DTO\SessionRequest;
use App\Services\Timetable\OccupancyRegistry;
use App\Services\Timetable\SlotGrid;
use App\Services\Timetable\Strategies\GreedyPlacementStrategy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GreedyPlacementStrategyTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_refuses_sessions_that_cross_a_break()
    {
        $grid = new SlotGrid([
            'days' => ['MONDAY'],
            'slot_step' => 30,
            'day_start' => '08:00',
            'day_end' => '14:00',
            'breaks' => [
                ['start' => '12:00', 'end' => '13:00'],
            ],
        ]);

        $registry = new OccupancyRegistry($grid);
        $strategy = new GreedyPlacementStrategy($registry, $grid);

        $level = Level::factory()->create();
        $classRoom = ClassRoom::factory()->create(['level_id' => $level->id, 'student_count' => 28]);
        $subject = Subject::factory()->create();
        $teacher = User::factory()->teacher()->create();
        $teacher->subjects()->attach($subject->id);
        $room = Room::factory()->create(['type' => 'CLASSROOM', 'capacity' => 40]);

        $plan = SubjectPlan::factory()->create([
            'level_id' => $level->id,
            'subject_id' => $subject->id,
            'sessions_per_week' => 1,
            'session_duration' => 120,
            'teaching_type' => 'THEORY',
        ]);

        $request = new SessionRequest(
            id: 1,
            levelId: $level->id,
            classRoom: $classRoom,
            subjectPlan: $plan,
            groupNumber: null,
            sessionsCount: 1,
            minCapacity: $classRoom->student_count,
            teacherIds: [$teacher->id],
            roomIds: [$room->id],
        );

        // On bloque les créneaux avant la pause pour forcer le premier point libre à 11:00.
        $registry->book($teacher->id, $room->id, $classRoom->id, null, 'MONDAY', 0, 6);

        $placed = $strategy->place($request);

        $this->assertNull($placed);
    }
}
