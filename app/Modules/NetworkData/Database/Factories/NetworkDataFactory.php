<?php

$factory->defineAs(App\Modules\NetworkData\Models\Atc::class, 'online', function ($faker) {
    return [
        'account_id' => factory(App\Models\Mship\Account::class)->create()->id,
        'callsign' => $faker->randomElement(['EGLL', 'EGKK', 'EGCC', 'EGBB']).'_'.$faker->randomElement(['N', 'S', 'F', '']).'_'.$faker->randomElement(['TWR', 'GND', 'DEL', 'APP']),
        'frequency' => $faker->randomFloat(3, 118, 134),
        'connected_at' => $faker->dateTime('6 hours ago'),
        'qualification_id' => App\Models\Mship\Qualification::parseVatsimATCQualification($faker->numberBetween(1, 12))->id,
        'facility_type' => $faker->numberBetween(1, 6),
    ];
});

$factory->defineAs(App\Modules\NetworkData\Models\Atc::class, 'offline', function ($faker) {
    return array_merge(
        factory(App\Modules\NetworkData\Models\Atc::class, 'online')->raw(),
        [
            'disconnected_at' => $faker->dateTimeBetween('-6 hours'),
        ]
    );
});
