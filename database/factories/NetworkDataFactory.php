<?php

use Faker\Generator as Faker;

$factory->define(\App\Models\NetworkData\Atc::class, function (Faker $faker) {
    return [
        'account_id' => function() {
            return factory(\App\Models\Mship\Account::class)->create()->id;
        },
        'callsign' => $faker->randomElement(['EGLL', 'EGKK', 'EGCC', 'EGBB']).'_'.$faker->randomElement(['N', 'S', 'F', '']).'_'.$faker->randomElement(['TWR', 'GND', 'DEL', 'APP']),
        'frequency' => $faker->randomFloat(3, 118, 134),
        'connected_at' => $faker->dateTime('6 hours ago'),
        'facility_type' => $faker->numberBetween(1, 6),
        'qualification_id' => function() {
            return factory(\App\Models\Mship\Qualification::class)->create()->id;
        }
    ];
});

$factory->defineAs(\App\Models\NetworkData\Atc::class, 'online', function (Faker $faker) {
    return [
        'callsign' => $faker->randomElement(['EGLL', 'EGKK', 'EGCC', 'EGBB']).'_'.$faker->randomElement(['N', 'S', 'F', '']).'_'.$faker->randomElement(['TWR', 'GND', 'DEL', 'APP']),
        'frequency' => $faker->randomFloat(3, 118, 134),
        'connected_at' => $faker->dateTime('6 hours ago'),
        'facility_type' => $faker->numberBetween(1, 6),
    ];
});

$factory->defineAs(\App\Models\NetworkData\Atc::class, 'offline', function (Faker $faker) {
    return array_merge(
        factory(\App\Models\NetworkData\Atc::class, 'online')->raw(),
        [
            'disconnected_at' => $faker->dateTimeBetween('-6 hours'),
        ]
    );
});
