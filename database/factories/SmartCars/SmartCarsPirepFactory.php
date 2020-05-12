<?php

use Faker\Generator as Faker;

$factory->define(\App\Models\Smartcars\Pirep::class, function (Faker $faker) {
    $bid = factory(\App\Models\Smartcars\Bid::class)->create();

    return [
        'bid_id' => function () use ($bid) {
            return $bid->id;
        },
        'aircraft_id' => $bid->flight->aircraft_id,
        'route' => $bid->flight->route,
        'flight_time' => $faker->time,
        'landing_rate' => $faker->numberBetween(-1000, 50),
        'comments' => $faker->optional(0.4)->paragraph,
        'fuel_used' => $faker->numberBetween(0, 80) . '.00',
        'log' => $faker->paragraph,
        'status' => 2,
        'passed' => 1,
        'pass_reason' => $faker->sentence,
        'failed_at' => null,
        'created_at' => \Carbon\Carbon::now(),
        'updated_at' => \Carbon\Carbon::now(),
    ];
});
