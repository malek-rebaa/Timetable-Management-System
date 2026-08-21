<?php

namespace Database\Factories;

use App\Models\Level;
use App\Models\Subject;
use App\Models\SubjectPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SubjectPlan>
 */
class SubjectPlanFactory extends Factory
{
    protected $model = SubjectPlan::class;

    public function definition(): array
    {
        return [
            'level_id' => Level::factory(),
            'subject_id' => Subject::factory(),
            'sessions_per_week' => fake()->numberBetween(1, 4),
            'session_duration' => fake()->randomElement([60, 90, 120]),
            'teaching_type' => fake()->randomElement(['THEORY', 'TP']),
        ];
    }
}
