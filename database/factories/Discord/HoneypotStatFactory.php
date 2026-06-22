<?php

declare(strict_types=1);

namespace Database\Factories\Discord;

use App\Models\Discord\HoneypotStat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Discord\HoneypotStat>
 */
class HoneypotStatFactory extends Factory
{
    protected $model = HoneypotStat::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'account_id' => (string) fake()->unique()->randomNumber(8),
        ];
    }
}
