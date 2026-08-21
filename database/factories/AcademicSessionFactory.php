<?php

namespace Database\Factories;

use App\Models\AcademicSession;
use App\Models\ClassRoom;
use App\Models\Room;
use App\Models\SubjectPlan;
use App\Models\Timetable;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AcademicSession>
 */
class AcademicSessionFactory extends Factory
{
    protected $model = AcademicSession::class;

    public function definition(): array
    {
        return [
            'timetable_id' => Timetable::factory(),
            'subject_plan_id' => SubjectPlan::factory(),
            'teacher_id' => User::factory()->teacher(),
            'class_room_id' => ClassRoom::factory(),
            'room_id' => Room::factory(),
            'day' => fake()->randomElement(['MONDAY', 'TUESDAY', 'WEDNESDAY', 'THURSDAY', 'FRIDAY']),
            'start_time' => '08:00',
            'end_time' => '09:00',
            'group_number' => null,
            'is_locked' => false,
        ];
    }
}
