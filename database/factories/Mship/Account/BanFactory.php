<?php

namespace Database\Factories\Mship\Account;

use App\Enums\BanTypeEnum;
use App\Models\Mship\Account;
use App\Models\Mship\Ban\Reason;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Model>
 */
class BanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition()
    {
        return [
            'account_id' => Account::factory(),
            'banned_by' => Account::factory(),
            'type' => BanTypeEnum::Local,
            'reason_id' => Reason::factory(),
            'reason_extra' => fake()->paragraph,
            'period_start' => now()->subDay(),
            'period_finish' => now()->addDays(fake()->randomDigitNotNull),
        ];
    }

    public function network()
    {
        return $this->state([
            'banned_by' => null,
            'type' => BanTypeEnum::Network,
            'reason_id' => null,
            'period_finish' => null,
        ]);
    }
}
