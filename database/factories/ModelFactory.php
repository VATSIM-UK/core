<?php

$factory->define(App\Models\Mship\Account::class, function ($faker) {
    return [
        'account_id' => rand(900000, 1300000),
        'name_first' => $faker->name,
        'name_last' => $faker->name,
        'is_invisible' => 0,
    ];
});