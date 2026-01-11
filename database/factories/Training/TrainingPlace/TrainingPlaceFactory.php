<?php

namespace Database\Factories\Training\TrainingPlace;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Training\TrainingPlace\TrainingPlace>
 */
class TrainingPlaceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'waiting_list_account_id' => null,
            'training_position_id' => null,
        ];
    }
}
