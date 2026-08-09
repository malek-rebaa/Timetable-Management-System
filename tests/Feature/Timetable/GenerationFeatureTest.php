<?php

namespace Tests\Feature\Timetable;

use App\Models\ClassRoom;
use App\Models\Level;
use App\Models\Room;
use App\Models\Subject;
use App\Models\SubjectPlan;
use App\Models\Timetable;
use App\Models\User;
use App\Services\Timetable\TimetableGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
