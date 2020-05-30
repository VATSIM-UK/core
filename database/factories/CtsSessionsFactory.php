<?php

/** @var \Illuminate\Database\Eloquent\Factory $factory */

use App\Models\Cts\Member;
use App\Models\Cts\Session;
use Faker\Generator as Faker;

$factory->define(Session::class, function (Faker $faker) {
    return [
        'rts_id' => $faker->randomNumber(),
        'position' => $faker->randomElement(['EGKK_APP', 'EGCC_APP', 'LON_SC_CTR', 'EGGP_GND']),
        'progress_sheet_id' => $faker->numberBetween(1, 25),
        'student_id' => factory(Member::class),
        'student_rating' => $faker->numberBetween(1, 9),
        'noShow' => 0,
        'no_avail_count' => 0,
        'session_done' => false,
    ];
});
