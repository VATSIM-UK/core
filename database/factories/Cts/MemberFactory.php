<?php

namespace Database\Factories\Cts;

use App\Models\Cts\Member;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

class MemberFactory extends Factory
{
    protected $model = Member::class;

    public function definition(): array
    {
        $joined = Carbon::now();
        $cid = $this->faker->unique()->numberBetween(810000, 1400000);

        return [
            'id' => $cid,
            'cid' => $cid,
            'name' => $this->faker->name,
            'joined' => $joined,
            'joined_div' => $joined->addDays(rand(-240, 0)),
        ];
    }
}
