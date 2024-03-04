<?php

namespace Database\Factories\Discord;

use App\Models\Discord\DiscordRoleRule;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Discord\DiscordQualificationRole>
 */
class DiscordRoleRuleFactory extends Factory
{
    protected $model = DiscordRoleRule::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'name' => fake()->sentence(2),
            'discord_id' => fake()->numberBetween(100000, 2000000),
        ];
    }
}
