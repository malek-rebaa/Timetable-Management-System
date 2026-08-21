<?php

namespace Tests\Unit\Timetable;

use App\Models\AcademicSession;
use App\Models\ClassRoom;
use App\Models\Level;
use App\Models\Room;
use App\Models\Subject;
use App\Models\SubjectPlan;
use App\Models\User;
use App\Services\Timetable\ConflictChecker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConflictCheckerTest extends TestCase
{
    use RefreshDatabase;

    protected ConflictChecker $checker;

    protected function setUp(): void
    {
        parent::setUp();
        $this->checker = app(ConflictChecker::class);
    }

    public function test_it_detects_teacher_overlap()
    {
        $teacher = User::factory()->create(['role' => 'TEACHER']);
        
        // Créer une séance existante
        AcademicSession::factory()->create([
            'teacher_id' => $teacher->id,
            'day' => 'MONDAY',
            'start_time' => '08:00',
            'end_time' => '10:00',
        ]);

        $candidate = new AcademicSession([
            'teacher_id' => $teacher->id,
            'day' => 'MONDAY',
            'start_time' => '09:00', // Chevauchement partiel
            'end_time' => '11:00',
        ]);

        $errors = $this->checker->checkOverlaps($candidate);
        
        $this->assertContains("L'enseignant a déjà une séance sur ce créneau.", $errors);
    }

    public function test_it_does_not_flag_touching_sessions_as_overlap()
    {
        $teacher = User::factory()->create(['role' => 'TEACHER']);

        AcademicSession::factory()->create([
            'teacher_id' => $teacher->id,
            'day' => 'MONDAY',
            'start_time' => '08:00',
            'end_time' => '10:00',
        ]);

        $candidate = new AcademicSession([
            'teacher_id' => $teacher->id,
            'day' => 'MONDAY',
            'start_time' => '10:00',
            'end_time' => '11:00',
        ]);

        $errors = $this->checker->checkOverlaps($candidate);

        $this->assertEmpty($errors);
    }

    public function test_it_ignores_itself_during_update()
    {
        $teacher = User::factory()->create(['role' => 'TEACHER']);
        
        $session = AcademicSession::factory()->create([
            'teacher_id' => $teacher->id,
            'day' => 'MONDAY',
            'start_time' => '08:00',
            'end_time' => '10:00',
        ]);

        $candidate = new AcademicSession([
            'teacher_id' => $teacher->id,
            'day' => 'MONDAY',
            'start_time' => '09:00', // Chevauchement
            'end_time' => '11:00',
        ]);

        // Si on passe l'ID de la session à ignorer, pas d'erreur sur l'enseignant
        $errors = $this->checker->checkOverlaps($candidate, $session->id);
        
        $this->assertEmpty($errors);
    }

    public function test_it_detects_room_capacity_issue()
    {
        $level = Level::factory()->create();
        $class = ClassRoom::factory()->create(['level_id' => $level->id, 'student_count' => 50]);
        $room = Room::factory()->create(['capacity' => 30, 'type' => 'CLASSROOM']);
        
        $subject = Subject::factory()->create();
        $plan = SubjectPlan::factory()->create([
            'level_id' => $level->id,
            'subject_id' => $subject->id,
            'teaching_type' => 'THEORY',
        ]);

        $candidate = new AcademicSession([
            'class_room_id' => $class->id,
            'room_id' => $room->id,
        ]);
        $candidate->setRelation('subjectPlan', $plan);
        $candidate->setRelation('classRoom', $class);
        $candidate->setRelation('room', $room);

        // La capacité 30 est < 50
        $errors = $this->checker->checkRoom($candidate);

        $this->assertCount(1, $errors);
        $this->assertStringContainsString('insuffisante pour 50 étudiants', $errors[0]);
    }
}
