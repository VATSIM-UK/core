<?php

namespace Database\Factories\Discord;

use App\Models\Discord\DiscordRole;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Discord\DiscordQualificationRole>
 */
class DiscordRoleFactory extends Factory
{
    protected $model = DiscordRole::class;

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
