<?php

namespace Database\Factories;

use App\Models\Timetable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Timetable>
 */
class TimetableFactory extends Factory
{
    protected $model = Timetable::class;

    public function definition(): array
    {
        return [
            'name' => fake()->sentence(3),
            'academic_year' => '2026-2027',
            'semester' => 'S1',
            'status' => 'PENDING',
        ];
    }
}
