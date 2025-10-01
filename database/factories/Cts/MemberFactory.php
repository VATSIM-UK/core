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

        return [
            'id' => rand(810000, 1400000),
            'cid' => rand(810000, 1400000),
            'name' => $this->faker->name,
            'joined' => $joined,
            'joined_div' => $joined->addDays(rand(-240, 0)),
        ];
    }
}
