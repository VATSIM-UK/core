<?php

use Faker\Generator as Faker;

$factory->define(\App\Models\Smartcars\Posrep::class, function (Faker $faker) {
    $bid = factory(\App\Models\Smartcars\Bid::class)->create();
    $dep = $faker->dateTimeThisYear;

    return [
        'bid_id' => function () use ($bid) {
            return $bid->id;
        },
        'aircraft_id' => $bid->flight->aircraft_id,
        'route' => $bid->flight->route,
        'altitude' => $faker->numberBetween(0, 10000),
        'heading_mag' => $faker->numberBetween(0, 359),
        'heading_true' => $faker->numberBetween(0, 359),
        'latitude' => $faker->latitude,
        'longitude' => $faker->longitude,
        'groundspeed' => $faker->numberBetween(0, 420),
        'distance_remaining' => $faker->numberBetween(0, 100),
        'phase' => $faker->numberBetween(0, 10),
        'time_departure' => $dep,
        'time_remaining' => '00:'.$faker->numberBetween(10, 59).':00',
        'time_arrival' => Carbon\Carbon::instance($dep)->addMinutes($faker->numberBetween(10, 59)),
        'network' => 'VATSIM',
        'created_at' => \Carbon\Carbon::now(),
        'updated_at' => \Carbon\Carbon::now(),
    ];
});
