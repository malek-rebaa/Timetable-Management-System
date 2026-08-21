<?php

namespace Tests\Unit\Timetable;

use App\Models\ClassRoom;
use App\Models\Level;
use App\Models\Subject;
use App\Models\SubjectPlan;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SessionRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_refuses_non_aligned_session_duration()
    {
        $level = Level::factory()->create();
        $classRoom = ClassRoom::factory()->create(['level_id' => $level->id]);
        $subject = Subject::factory()->create();

        $plan = SubjectPlan::factory()->create([
            'level_id' => $level->id,
            'subject_id' => $subject->id,
            'sessions_per_week' => 1,
            'session_duration' => 95,
            'teaching_type' => 'THEORY',
        ]);

        $request = new \App\Services\Timetable\DTO\SessionRequest(
            id: 1,
            levelId: $level->id,
            classRoom: $classRoom,
            subjectPlan: $plan,
            groupNumber: null,
            sessionsCount: 1,
            minCapacity: 25,
            teacherIds: [],
            roomIds: [],
        );

        $this->expectException(\DomainException::class);
        $request->durationSlots(30);
    }
}
