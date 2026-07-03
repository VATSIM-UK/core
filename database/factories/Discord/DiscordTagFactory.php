<?php

declare(strict_types=1);

namespace Database\Factories\Discord;

use App\Models\Discord\DiscordTag;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Discord\DiscordTag>
 */
class DiscordTagFactory extends Factory
{
    protected $model = DiscordTag::class;

    public function definition(): array
    {
        return [
            'key' => fake()->unique()->word(),
            'title' => fake()->sentence(3),
            'value' => fake()->sentence(),
        ];
    }
}
