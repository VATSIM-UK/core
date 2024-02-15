<?php

namespace Database\Factories\Mship\Account;

use App\Models\Atc\PositionGroup;
use App\Models\Mship\Account;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Mship\Account\EndorsementRequest>
 */
class EndorsementRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'account_id' => Account::factory()->create()->id,
            'endorsable_type' => 'App\Models\Atc\PositionGroup',
            'endorsable_id' => PositionGroup::factory()->create()->id,
            'requested_by' => Account::factory()->create()->id,
            'actioned_at' => null,
            'actioned_type' => null,
            'notes' => null,
        ];
    }
}
