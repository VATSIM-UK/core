<?php

namespace Database\Factories\Discord;

use App\Models\Mship\Qualification;
use App\Models\Mship\State;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Discord\DiscordQualificationRole>
 */
class DiscordQualificationRoleFactory extends Factory
{
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
            'qualification_id' => factory(Qualification::class),
            'state_id' => factory(State::class),
        ];
    }
}
