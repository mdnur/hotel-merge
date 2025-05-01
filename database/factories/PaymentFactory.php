<?php

namespace Database\Factories;

use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'payment_type_id' => $this->faker->numberBetween(1, 3),
            'advance' => $this->faker->numberBetween(100, 1000),
            'Last3Digit' => $this->faker->numberBetween(100, 999),
        ];
    }
}
