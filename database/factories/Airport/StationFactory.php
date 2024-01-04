<?php

use Faker\Generator as Faker;

$factory->define(\App\Models\Atc\Position::class, function (Faker $faker) {
    return [
        'callsign' => strtoupper($faker->word).'_'.$faker->randomElement(['TWR', 'GND', 'DEL', 'APP', 'ATIS', 'CTR']),
        'name' => ucfirst($faker->word).' '.$faker->randomElement(['Tower', 'Ground', 'Delivery', 'Approach', 'Information', 'Control']),
        'frequency' => $faker->randomFloat(3, 0, 130),
        'type' => $faker->numberBetween(1, 8),
        'sub_station' => false,
    ];
});
