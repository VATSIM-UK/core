<?php

namespace Database\Factories\Training\TrainingPlace;

use App\Enums\AvailabilityLogEvent;
use App\Models\Training\TrainingPlace\TrainingPlace;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Training\TrainingPlace\AvailabilityLogEntry>
 */
class AvailabilityLogEntryFactory extends Factory
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
            'event' => AvailabilityLogEvent::Added,
            'slot_from' => now()->addDay(),
            'slot_to' => now()->addDay()->addHours(3),
            'created_at' => now(),
            'superseded_at' => null,
        ];
    }

    public function merged(): static
    {
        return $this->state(fn (array $attributes) => [
            'event' => AvailabilityLogEvent::Merged,
        ]);
    }

    public function edited(): static
    {
        return $this->state(fn (array $attributes) => [
            'event' => AvailabilityLogEvent::Edited,
        ]);
    }

    public function superseded(): static
    {
        return $this->state(fn (array $attributes) => [
            'superseded_at' => now(),
        ]);
    }
}
