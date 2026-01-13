<?php

namespace Database\Factories\Training\TrainingPosition;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Training\TrainingPosition\TrainingPosition>
 */
class TrainingPositionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'cts_positions' => null,
        ];
    }

    /**
     * Indicate that the training position should have CTS positions.
     *
     * @param  array<int>|null  $positionIds
     */
    public function withCtsPositions(?array $positionIds = null): Factory
    {
        return $this->state(function (array $attributes) use ($positionIds) {
            return [
                'cts_positions' => $positionIds ?? [1, 2, 3],
            ];
        });
    }
}
