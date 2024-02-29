<?php

namespace Database\Factories\Mship\Account;

use App\Models\Atc\PositionGroup;
use App\Models\Model;
use App\Models\Mship\Account;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Model>
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
            'account_id' => Account::factory(),
            'endorsable_type' => PositionGroup::class,
            'endorsable_id' => PositionGroup::factory(),
            'expires_at' => null,
            'created_by' => Account::factory(),
        ];
    }

    public function soloEndorsement()
    {
        return $this->state([
            'expires_at' => now()->addDays(30),
        ]);
    }
}
