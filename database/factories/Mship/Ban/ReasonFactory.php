<?php

namespace Database\Factories\Mship\Ban;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Mship\Ban\Reason>
 */
class ReasonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => fake()->words(2, true),
            'reason_text' => fake()->paragraph,
            'period_amount' => fake()->randomDigitNot(0),
            'period_unit' => fake()->randomElement(['M', 'H', 'D']),
            'created_at' => fake()->dateTime(),
            'updated_at' => fake()->dateTime(),
        ];
    }
}
