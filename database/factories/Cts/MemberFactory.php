<?php

use Carbon\Carbon;
use Faker\Generator as Faker;

$factory->define(App\Models\Cts\Member::class, function (Faker $faker) {
    $joined = Carbon::now();

    return [
        'id' => rand(810000, 1400000),
        'cid' => rand(810000, 1400000),
        'name' => $faker->name,
        'joined' => $joined,
        'joined_div' => $joined->addDays(rand(-240, 0)),
    ];
});
