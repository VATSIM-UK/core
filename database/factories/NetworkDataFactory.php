<?php

$factory->defineAs(\App\Models\NetworkData\Atc::class, 'online', function ($faker) {
    return [
        'callsign' => $faker->randomElement(['EGLL', 'EGKK', 'EGCC', 'EGBB']).'_'.$faker->randomElement(['N', 'S', 'F', '']).'_'.$faker->randomElement(['TWR', 'GND', 'DEL', 'APP']),
        'frequency' => $faker->randomFloat(3, 118, 134),
        'connected_at' => $faker->dateTime('6 hours ago'),
        'facility_type' => $faker->numberBetween(1, 6),
    ];
});

$factory->defineAs(\App\Models\NetworkData\Atc::class, 'offline', function ($faker) {
    return array_merge(
        factory(\App\Models\NetworkData\Atc::class, 'online')->raw(),
        [
            'disconnected_at' => $faker->dateTimeBetween('-6 hours'),
        ]
    );
});
