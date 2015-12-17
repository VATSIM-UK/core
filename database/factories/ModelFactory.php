<?php

$factory->defineAs(App\Models\Mship\Account::class, "normal", function ($faker) {
    return [
        'account_id' => 980234,
        'name_first' => $faker->name,
        'name_last' => $faker->name,
        'status' => App\Models\Account::STATUS_ACTIVE,
        'is_invisible' => 0,
    ];
});

$factory->defineAs(App\Models\Mship\Account::class, "suspended_network", function ($faker) {
    return [
        'account_id' => rand(900000, 1300000),
        'name_first' => $faker->name,
        'name_last' => $faker->name,
        'status' => App\Models\Account::STATUS_NETWORK_SUSPENDED,
        'is_invisible' => 0,
    ];
});

$factory->defineAs(App\Models\Mship\Account::class, "suspended_local", function ($faker) {
    return [
        'account_id' => rand(900000, 1300000),
        'name_first' => $faker->name,
        'name_last' => $faker->name,
        'status' => App\Models\Account::STATUS_SYSTEM_BANNED,
        'is_invisible' => 0,
    ];
});