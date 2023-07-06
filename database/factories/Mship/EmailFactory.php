<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Mship\Account\Email::class, function (Faker $faker) {
    return [
        'id' => $faker->numberBetween(1, 100000),
        'account_id' => function () {
            return \App\Models\Mship\Account::factory()->create()->id;
        },
        'email' => $faker->email,
        'verified_at' => $faker->dateTime(),
        'created_at' => $faker->dateTime(),
        'updated_at' => $faker->dateTime(),
    ];
});

$factory->state(\App\Models\Mship\Account\Email::class, 'unverified', function (Faker $faker) {
    return [
        'verified_at' => null,
    ];
});

$factory->state(\App\Models\Mship\Account\Email::class, 'verified', function () {
    return [
        'verified_at' => now(),
    ];
});
