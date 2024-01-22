<?php

namespace Database\Factories\Mship\Account;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class EndorsementFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'account_id' => \App\Models\Mship\Account::factory(),
            'position_group_id' => \App\Models\Atc\PositionGroup::factory(),
            'expired_at' => null,
            'created_by' => \App\Models\Mship\Account::factory(),
        ];
    }

    public function soloEndorsement()
    {
        return $this->state([
            'expires_at' => now()->addDays(30),
        ]);
    }
}
