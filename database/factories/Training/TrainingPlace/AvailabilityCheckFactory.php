<?php

namespace Database\Factories\Training\TrainingPlace;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Training\TrainingPlace\AvailabilityCheck>
 */
class AvailabilityCheckFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'training_place_id' => \App\Models\Training\TrainingPlace\TrainingPlace::factory(),
            'status' => 'passed',
        ];
    }

    public function passed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'passed',
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
        ]);
    }
}
