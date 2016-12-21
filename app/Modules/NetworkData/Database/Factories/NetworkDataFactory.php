<?php

$factory->defineAs(App\Modules\NetworkData\Models\Atc::class, 'online', function ($faker) {
    return [
        'account_id'           => factory(App\Models\Mship\Account::class)->create()->account_id,
        'callsign'             => $faker->randomElement(['EGLL', 'EGKK', 'EGCC', 'EGBB']).'_'.$faker->randomElement(['N', 'S', 'F', '']).'_'.$faker->randomElement(['TWR', 'GND', 'DEL', 'APP']),
        'connected_at'         => $faker->dateTime('6 hours ago'),
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
