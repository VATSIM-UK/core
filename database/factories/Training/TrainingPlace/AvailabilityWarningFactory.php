<?php

namespace Database\Factories\Training\TrainingPlace;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Training\TrainingPlace\AvailabilityWarning>
 */
class AvailabilityWarningFactory extends Factory
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
            'availability_check_id' => \App\Models\Training\TrainingPlace\AvailabilityCheck::factory(),
            'status' => 'pending',
            'expires_at' => now()->addDays(5),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    public function resolved(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'expired',
        ]);
    }
}
