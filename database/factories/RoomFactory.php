<?php

namespace Database\Factories;

use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Room>
 */
class RoomFactory extends Factory
{
    protected $model = Room::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->bothify('Room ###'),
            'capacity' => fake()->numberBetween(20, 120),
            'type' => fake()->randomElement(['CLASSROOM', 'LABORATORY', 'AMPHITHEATER']),
        ];
    }
}
