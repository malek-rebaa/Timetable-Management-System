<?php

namespace Tests\Feature\Timetable;

use App\Jobs\GenerateTimetableJob;
use App\Models\ClassRoom;
use App\Models\Level;
use App\Models\Room;
use App\Models\Subject;
use App\Models\SubjectPlan;
use App\Models\Timetable;
use App\Models\User;
use App\Services\Timetable\TimetableGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\TestCase;

class GenerationFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_timetable_generation()
    {
        // On crée un setup minimal : 1 niveau, 10 classes, 3 matières, 20 profs, 5 salles
        $level = Level::factory()->create();
        
        $classes = ClassRoom::factory()->count(10)->create(['level_id' => $level->id, 'student_count' => 30]);
        $subjects = Subject::factory()->count(3)->create();
        
        foreach ($subjects as $subject) {
            SubjectPlan::factory()->create([
                'level_id' => $level->id,
                'subject_id' => $subject->id,
                'sessions_per_week' => 2,
                'session_duration' => 120, // 2h
                'teaching_type' => 'THEORY'
            ]);
        }

        $teachers = User::factory()->count(20)->create(['role' => 'TEACHER']);
        // Attacher tous les profs à toutes les matières pour simplifier le test
        foreach ($teachers as $teacher) {
            $teacher->subjects()->attach($subjects->pluck('id'));
        }

        Room::factory()->count(5)->create(['type' => 'CLASSROOM', 'capacity' => 40]);

        $timetable = Timetable::create([
            'name' => 'Test Generation',
            'status' => 'RUNNING'
        ]);

        $generator = app(TimetableGenerator::class);
        $result = $generator->generate($timetable);

        $this->assertTrue($result['success']);
        
        // 10 classes * 3 matières * 2 sessions = 60 séances
        $this->assertEquals(60, $result['placed']);
        
        // Vérifier que la base contient 60 séances
        $this->assertEquals(60, $timetable->academicSessions()->count());
        $this->assertEquals('COMPLETED', $timetable->fresh()->status);
    }

    public function test_generation_fails_when_no_teacher_is_available()
    {
        $level = Level::factory()->create();
        $class = ClassRoom::factory()->create(['level_id' => $level->id, 'student_count' => 28]);
        Subject::factory()->create();

        SubjectPlan::factory()->create([
            'level_id' => $level->id,
            'sessions_per_week' => 2,
            'session_duration' => 60,
            'teaching_type' => 'THEORY',
        ]);

        Room::factory()->create(['type' => 'CLASSROOM', 'capacity' => 40]);

        $timetable = Timetable::create([
            'name' => 'Impossible Generation',
            'status' => 'RUNNING',
        ]);

        $generator = app(TimetableGenerator::class);
        $result = $generator->generate($timetable);

        $this->assertFalse($result['success']);
        $this->assertSame(0, $result['placed']);
        $this->assertSame(0, $timetable->academicSessions()->count());
        $this->assertSame('FAILED', $timetable->fresh()->status);
    }

    public function test_generation_fails_when_no_room_is_available()
    {
        $level = Level::factory()->create();
        $subject = Subject::factory()->create();
        $teacher = User::factory()->teacher()->create();
        $teacher->subjects()->attach($subject->id);

        $class = ClassRoom::factory()->create(['level_id' => $level->id, 'student_count' => 28]);

        SubjectPlan::factory()->create([
            'level_id' => $level->id,
            'subject_id' => $subject->id,
            'sessions_per_week' => 1,
            'session_duration' => 60,
            'teaching_type' => 'THEORY',
        ]);

        $timetable = Timetable::create([
            'name' => 'No Room Generation',
            'status' => 'RUNNING',
        ]);

        $generator = app(TimetableGenerator::class);
        $result = $generator->generate($timetable);

        $this->assertFalse($result['success']);
        $this->assertSame(0, $result['placed']);
        $this->assertSame(0, $timetable->academicSessions()->count());
        $this->assertSame('FAILED', $timetable->fresh()->status);
    }

    public function test_locked_sessions_are_preserved_during_regeneration()
    {
        $level = Level::factory()->create();
        $class = ClassRoom::factory()->create(['level_id' => $level->id, 'student_count' => 30]);
        $subject = Subject::factory()->create();
        $teacher = User::factory()->teacher()->create();
        $teacher->subjects()->attach($subject->id);
        $room = Room::factory()->create(['type' => 'CLASSROOM', 'capacity' => 40]);

        $plan = SubjectPlan::factory()->create([
            'level_id' => $level->id,
            'subject_id' => $subject->id,
            'sessions_per_week' => 2,
            'session_duration' => 60,
            'teaching_type' => 'THEORY',
        ]);

        $timetable = Timetable::create([
            'name' => 'Locked Regeneration',
            'status' => 'RUNNING',
        ]);

        $locked = $timetable->academicSessions()->create([
            'subject_plan_id' => $plan->id,
            'teacher_id' => $teacher->id,
            'class_room_id' => $class->id,
            'room_id' => $room->id,
            'day' => 'MONDAY',
            'start_time' => '08:00:00',
            'end_time' => '09:00:00',
            'group_number' => null,
            'is_locked' => true,
        ]);

        $generator = app(TimetableGenerator::class);
        $result = $generator->generate($timetable);

        $this->assertTrue($result['success']);
        $this->assertTrue($locked->fresh()->is_locked);
        $this->assertSame('08:00:00', $locked->fresh()->start_time);
        $this->assertSame('09:00:00', $locked->fresh()->end_time);
        $this->assertGreaterThanOrEqual(1, $timetable->academicSessions()->where('is_locked', true)->count());
        $this->assertSame('COMPLETED', $timetable->fresh()->status);
    }

    public function test_generate_route_queues_job_asynchronously()
    {
        Queue::fake();

        $user = User::factory()->teacher()->create();
        $this->actingAs($user);

        $level = Level::factory()->create();
        $class = ClassRoom::factory()->create(['level_id' => $level->id]);

        $response = $this->post(route('timetable.generate'), [
            'name' => 'Async EDT',
            'filter_class' => $class->id,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('timetables', [
            'name' => 'Async EDT',
            'status' => 'PENDING',
        ]);

        Queue::assertPushed(GenerateTimetableJob::class, function (GenerateTimetableJob $job) {
            return isset($job->timetableId) && ! empty($job->options['class_room_id']);
        });
    }
}
