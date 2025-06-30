<?php

// use Carbon\Carbon;
// use Faker\Generator as Faker;

// $factory->define(App\Models\Cts\Member::class, function (Faker $faker) {
//     $joined = Carbon::now();

//     return [
//         'id' => rand(810000, 1400000),
//         'cid' => rand(810000, 1400000),
//         'name' => $faker->name,
//         'joined' => $joined,
//         'joined_div' => $joined->addDays(rand(-240, 0)),
//     ];
// });

namespace Database\Factories\Cts;

use App\Models\Cts\Member;
use Illuminate\Database\Eloquent\Factories\Factory;
use Carbon\Carbon;
use Faker\Generator as Faker;

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