<?php

namespace Database\Factories\Atc;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Atc\Position>
 */
class PositionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'callsign' => strtoupper(fake()->word).'_'.fake()->randomElement(['TWR', 'GND', 'DEL', 'APP', 'ATIS', 'CTR']),
            'name' => ucfirst(fake()->word).' '.fake()->randomElement(['Tower', 'Ground', 'Delivery', 'Approach', 'Information', 'Control']),
            'frequency' => fake()->randomFloat(3, 0, 130),
            'type' => fake()->numberBetween(1, 8),
            'sub_station' => false,
        ];
    }

    public function temporarilyEndorsable(): self
    {
        return $this->state([
            'temporarily_endorsable' => true,
        ]);
    }
}
