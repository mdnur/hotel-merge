<?php

namespace Database\Factories;

use App\Models\Reservation;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Reservation>
 */
class ReservationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'reservation_no' => $this->faker->unique()->randomNumber(4),
            'booking_date' => $this->faker->dateTimeBetween('now', '+10 days'),
            'customer_id' => $this->faker->numberBetween(1, 10),
            'payment_id' => $this->faker->numberBetween(1, 10),
            'check_in_date' => $this->faker->dateTimeBetween('now', '+03 days'),
            'check_out_date' => $this->faker->dateTimeBetween('now', '+06 days'),
            'total_rent' => $this->faker->randomFloat(2, 100, 1000),
            'note' => $this->faker->optional()->sentence,
            'user_id' => $this->faker->numberBetween(1, 5),
        ];
    }
}
