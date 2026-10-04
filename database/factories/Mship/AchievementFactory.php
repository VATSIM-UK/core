<?php

namespace Database\Factories\Mship;

use App\Models\Mship\Account;
use App\Models\Mship\Achievement;
use Illuminate\Database\Eloquent\Factories\Factory;

class AchievementFactory extends Factory
{
    protected $model = Achievement::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(8),
            'image' => 'achievements/achievement.png',
            'created_by' => Account::factory(),
            'updated_by' => Account::factory(),
            'deleted_by' => null,
        ];
    }
}
