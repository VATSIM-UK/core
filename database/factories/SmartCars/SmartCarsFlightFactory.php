<?php

use Faker\Generator as Faker;

$factory->define(\App\Models\Smartcars\Flight::class, function (Faker $faker) {
    return [
        'code' => strtoupper($faker->randomLetter).$faker->numberBetween(0, 50),
        'name' => $faker->words(3, true),
        'description' => $faker->paragraph,
        'image' => $faker->image(),
        'featured' => $faker->optional(0.1, 0)->passthrough(1),
        'flightnum' => 1,
        'departure_id' => function () {
            return factory(\App\Models\Smartcars\Airport::class)->create()->id;
        },
        'arrival_id' => function () {
            return factory(\App\Models\Smartcars\Airport::class)->create()->id;
        },
        'route' => $faker->paragraph,
        'route_details' => $faker->paragraph,
        'aircraft_id' => function () {
            return factory(\App\Models\Smartcars\Aircraft::class)->create()->id;
        },
        'cruise_altitude' => $faker->numberBetween(1000, 30000),
        'distance' => $faker->randomFloat(2, 10, 160),
        'flight_time' => $faker->randomFloat(2, 0, 5),
        'notes' => $faker->paragraph,
        'enabled' => 1,
        'created_at' => \Carbon\Carbon::now(),
        'updated_at' => \Carbon\Carbon::now(),
    ];
});
