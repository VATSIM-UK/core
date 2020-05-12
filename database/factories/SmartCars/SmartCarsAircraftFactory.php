<?php

use Faker\Generator as Faker;

$factory->define(\App\Models\Smartcars\Aircraft::class, function (Faker $faker) {
    return [
        'icao' => $faker->randomElement(['L', 'M', 'H']),
        'name' => 'Cessna',
        'fullname' => $faker->words(2, true),
        'registration' => 'G'.strtoupper($faker->randomLetter.$faker->randomLetter.$faker->randomLetter.$faker->randomLetter),
        'range_nm' => $faker->numberBetween(0, 1000),
        'weight_kg' => $faker->numberBetween(1000, 10000),
        'cruise_altitude' => $faker->numberBetween(7000, 40000),
        'max_passengers' => $faker->numberBetween(1, 450),
        'max_cargo_kg' => 0,
        'created_at' => \Carbon\Carbon::now(),
        'updated_at' => \Carbon\Carbon::now(),
    ];
});
