<?php

use Faker\Generator as Faker;

$factory->define(\App\Models\Mship\Ban\Reason::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'reason_text' => $faker->sentence,
        'period_amount' => 28,
        'period_unit' => 'D',
    ];
});
