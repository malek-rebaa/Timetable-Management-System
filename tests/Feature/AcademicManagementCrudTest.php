<?php

namespace Tests\Feature;

use App\Models\ClassRoom;
use App\Models\Level;
use App\Models\Room;
use App\Models\Subject;
use App\Models\SubjectPlan;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AcademicManagementCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_can_manage_levels_classes_subjects_plans_and_rooms(): void
    {
        $this->actingAs(User::factory()->admin()->create());

        $this->post(route('levels.store'), ['name' => 'Licence 1'])->assertRedirect();
        $level = Level::firstOrFail();
        $this->put(route('levels.update', $level), ['name' => 'Licence Informatique 1'])->assertRedirect();

        $this->post(route('classes.store'), [
            'level_id' => $level->id,
            'name' => 'INFO1A',
            'student_count' => 30,
        ])->assertRedirect();
        $classRoom = ClassRoom::firstOrFail();
        $this->put(route('classes.update', $classRoom), [
            'level_id' => $level->id,
            'name' => 'INFO1B',
            'student_count' => 32,
        ])->assertRedirect();

        $this->post(route('subjects.store'), ['name' => 'Algorithmique'])->assertRedirect();
        $subject = Subject::firstOrFail();
        $this->put(route('subjects.update', $subject), ['name' => 'Algorithmique avancée'])->assertRedirect();

        $this->post(route('subject-plans.store'), [
            'level_id' => $level->id,
            'subject_id' => $subject->id,
            'sessions_per_week' => 2,
            'session_duration' => 90,
            'teaching_type' => 'THEORY',
        ])->assertRedirect();
        $plan = SubjectPlan::firstOrFail();
        $this->put(route('subject-plans.update', $plan), [
            'level_id' => $level->id,
            'subject_id' => $subject->id,
            'sessions_per_week' => 3,
            'session_duration' => 60,
            'teaching_type' => 'THEORY',
        ])->assertRedirect();

        $this->post(route('rooms.store'), [
            'name' => 'Lab 01',
            'type' => 'LABORATORY',
            'capacity' => 25,
        ])->assertRedirect();
        $room = Room::firstOrFail();
        $this->put(route('rooms.update', $room), [
            'name' => 'Lab 02',
            'type' => 'LABORATORY',
            'capacity' => 30,
        ])->assertRedirect();

        $this->assertDatabaseHas('levels', ['name' => 'Licence Informatique 1']);
        $this->assertDatabaseHas('class_rooms', ['name' => 'INFO1B', 'student_count' => 32]);
        $this->assertDatabaseHas('subjects', ['name' => 'Algorithmique avancée']);
        $this->assertDatabaseHas('subject_plans', ['sessions_per_week' => 3, 'session_duration' => 60]);
        $this->assertDatabaseHas('rooms', ['name' => 'Lab 02', 'capacity' => 30]);

        $this->delete(route('rooms.destroy', $room))->assertRedirect();
        $this->delete(route('subject-plans.destroy', $plan))->assertRedirect();
        $this->delete(route('subjects.destroy', $subject))->assertRedirect();
        $this->delete(route('classes.destroy', $classRoom))->assertRedirect();
        $this->delete(route('levels.destroy', $level))->assertRedirect();

        $this->assertDatabaseCount('rooms', 0);
        $this->assertDatabaseCount('subject_plans', 0);
        $this->assertDatabaseCount('subjects', 0);
        $this->assertDatabaseCount('class_rooms', 0);
        $this->assertDatabaseCount('levels', 0);
    }

    public function test_programme_must_be_unique_and_its_duration_must_follow_the_grid(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $level = Level::factory()->create();
        $subject = Subject::factory()->create();

        $payload = [
            'level_id' => $level->id,
            'subject_id' => $subject->id,
            'sessions_per_week' => 2,
            'session_duration' => 90,
            'teaching_type' => 'THEORY',
        ];

        $this->post(route('subject-plans.store'), $payload)->assertSessionHasNoErrors();
        $this->post(route('subject-plans.store'), $payload)->assertSessionHasErrors('subject_id');
        $this->post(route('subject-plans.store'), array_replace($payload, ['teaching_type' => 'TP', 'session_duration' => 95]))
            ->assertSessionHasErrors('session_duration');
    }

    public function test_teacher_cannot_access_administration_crud_screens(): void
    {
        $this->actingAs(User::factory()->teacher()->create());

        $this->get(route('levels.index'))->assertForbidden();
        $this->get(route('subjects.index'))->assertForbidden();
        $this->get(route('rooms.index'))->assertForbidden();
    }

    public function test_administrator_assigns_subjects_to_a_teacher(): void
    {
        $this->actingAs(User::factory()->admin()->create());
        $mathematics = Subject::factory()->create(['name' => 'Mathématiques']);
        $physics = Subject::factory()->create(['name' => 'Physique']);

        $this->post(route('teachers.store'), [
            'first_name' => 'Amine',
            'last_name' => 'Ben Ali',
            'phone' => '20000000',
            'subject_ids' => [$mathematics->id, $physics->id],
        ])->assertRedirect(route('teachers.index'));

        $teacher = User::where('role', 'TEACHER')->firstOrFail();
        $this->assertDatabaseHas('teacher_subject', [
            'teacher_id' => $teacher->id,
            'subject_id' => $mathematics->id,
        ]);
        $this->assertDatabaseHas('teacher_subject', [
            'teacher_id' => $teacher->id,
            'subject_id' => $physics->id,
        ]);
    }
}
