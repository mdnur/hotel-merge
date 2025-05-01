<?php

namespace Database\Factories;

use App\Models\Room;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Room>
 */
class RoomFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'room_type_id' => $this->faker->numberBetween(1, 3),
            'check_in' => $this->faker->dateTimeBetween('now', '+03 days'),
            'check_out' => $this->faker->dateTimeBetween('now', '+06 days'),
            'reservation_id' => $this->faker->numberBetween(1, 5),
            'quantity' => $this->faker->numberBetween(1, 3),
            'rent' => $this->faker->randomElement([2000, 2250, 3000]),
        ];
    }
}
