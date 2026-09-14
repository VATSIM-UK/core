<?php

namespace Database\Factories\Cts;

use App\Models\Cts\Member;
use App\Models\Mship\Account;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class MemberFactory extends Factory
{
    protected $model = Member::class;

    /**
     * CTS assigns member `id` independently of `cid` (see HasCTSAccount::generateCTSInternalID) -
     * they must never be assumed equal, so the default state deliberately draws them from
     * non-overlapping ranges.
     */
    public function definition(): array
    {
        $joined = Carbon::now();

        return [
            'id' => $this->faker->unique()->numberBetween(2000000, 2999999),
            'cid' => $this->faker->unique()->numberBetween(810000, 1400000),
            'name' => $this->faker->name,
            'joined' => $joined,
            'joined_div' => $joined->addDays(rand(-240, 0)),
        ];
    }

    /**
     * Link this member to a real account via `cid`, while keeping `id` deliberately
     * different so tests exercise the `cid` translation instead of assuming id === cid.
     */
    public function forAccount(Account $account): static
    {
        return $this->state(fn () => [
            'cid' => $account->id,
            'id' => $account->id + 5000000,
        ]);
    }
}
