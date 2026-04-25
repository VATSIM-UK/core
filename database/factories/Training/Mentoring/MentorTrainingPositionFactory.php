<?php

namespace Database\Factories\Training\Mentoring;

use App\Models\Mship\Account;
use App\Models\Training\Mentoring\MentorTrainingPosition;
use App\Models\Training\TrainingPosition\TrainingPosition;
use Illuminate\Database\Eloquent\Factories\Factory;

class MentorTrainingPositionFactory extends Factory
{
    protected $model = MentorTrainingPosition::class;

    public function definition(): array
    {
        return [
            'account_id' => Account::factory(),
            'mentorable_type' => TrainingPosition::class,
            'mentorable_id' => TrainingPosition::factory(),
            'created_by' => Account::factory(),
        ];
    }

    public function qualification(): static
    {
        return $this->state(fn (array $attributes) => [
            'mentorable_type' => \App\Models\Mship\Qualification::class,
            'mentorable_id' => \App\Models\Mship\Qualification::factory(),
        ]);
    }
}
