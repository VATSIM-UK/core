<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Airport::class, function (Faker $faker) {
    return [
        'icao' => $faker->randomLetter . $faker->randomLetter . $faker->randomLetter . $faker->randomLetter,
        'iata' => $faker->randomLetter . $faker->randomLetter . $faker->randomLetter,
        'name' => $faker->city,
        'fir_type' => $faker->randomElement([1, 2]),
        'major' => $faker->optional(0.7, false)->passthrough(true),
        'latitude' => $faker->latitude,
        'longitude' => $faker->longitude,
        'elevation' => $faker->numberBetween(0, 1500),
        'description' => $faker->optional(0.5)->randomElement([$faker->text, $faker->randomHtml(2, 3)]),
        'departure_procedures' => $faker->optional(0.5)->randomElement([$faker->text, $faker->randomHtml(2, 3)]),
        'arrival_procedures' => $faker->optional(0.5)->randomElement([$faker->text, $faker->randomHtml(2, 3)]),
        'vfr_procedures' => $faker->optional(0.5)->randomElement([$faker->text, $faker->randomHtml(2, 3)]),
        'other_information' => $faker->optional(0.5)->randomElement([$faker->text, $faker->randomHtml(2, 3)]),
    ];
});
