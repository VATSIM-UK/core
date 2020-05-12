<?php

use Faker\Generator as Faker;

$factory->define(\App\Models\Smartcars\Airport::class, function (Faker $faker) {
    return [
        'icao' => strtoupper($faker->randomLetter.$faker->randomLetter.$faker->randomLetter.$faker->randomLetter),
        'name' => $faker->city,
        'country' => $faker->state,
        'latitude' => $faker->latitude,
        'longitude' => $faker->longitude,
        'created_at' => \Carbon\Carbon::now(),
        'updated_at' => \Carbon\Carbon::now(),
    ];
});
