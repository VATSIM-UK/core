<?php

namespace Database\Factories\Training\TrainingPlace;

use App\Enums\AvailabilityCheckStatus;
use App\Models\Training\TrainingPlace\TrainingPlace;
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
            'training_place_id' => TrainingPlace::factory(),
            'status' => AvailabilityCheckStatus::Passed,
        ];
    }

    public function passed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AvailabilityCheckStatus::Passed,
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AvailabilityCheckStatus::Failed,
        ]);
    }

    public function onLeave(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => AvailabilityCheckStatus::OnLeave,
        ]);
    }
}
