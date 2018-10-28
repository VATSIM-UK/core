<?php

use App\Models\Cts\Member;
use Carbon\Carbon;
use Faker\Generator as Faker;

$factory->define(App\Models\Cts\Booking::class, function (Faker $faker) {
    $from = $faker->time();

    return [
        'date' => $faker->dateTimeInInterval('+1 YEAR')->format('Y-m-d'),
        'from' => $from,
        'to' => Carbon::createFromTimeString($from)->addHours(rand(1, 4))->toTimeString(),
        'position' => $faker->randomElement(['EGKK_APP', 'EGCC_APP', 'LON_SC_CTR', 'EGGP_GND']),
        'member_id' => factory(Member::class)->create()->id,
        'type' => $faker->randomElement(['BK', 'EX', 'ME']),
    ];
});
