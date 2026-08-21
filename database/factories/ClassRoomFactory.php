<?php

namespace Database\Factories;

use App\Models\ClassRoom;
use App\Models\Level;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ClassRoom>
 */
class ClassRoomFactory extends Factory
{
    protected $model = ClassRoom::class;

    public function definition(): array
    {
        return [
            'level_id' => Level::factory(),
            'name' => fake()->unique()->bothify('Class ?#'),
            'student_count' => fake()->numberBetween(20, 45),
        ];
    }
}
