<?php

$factory->define(App\Models\Mship\Account::class, function ($faker) {
    return [
        'id'           => rand(900000, 1300000),
        'name_first'   => $faker->name,
        'name_last'    => $faker->name,
        'email'        => $faker->email,
        'is_invisible' => 0,
    ];
});

$factory->define(App\Models\Mship\Account\Email::class, function ($faker) {
    return [
        'id' => $faker->numberBetween(1, 100000),
        'email' => $faker->email,
        'verified_at' => $faker->dateTime(),
        'created_at' => $faker->dateTime(),
        'updated_at' => $faker->dateTime(),
    ];
});

$factory->define(App\Models\Mship\Qualification::class, function ($faker) {
    return [
        "code" => $faker->bothify("?##"),
        "name_small" => $faker->word,
        "name_long"  => $faker->word,
        "name_grp"   => $faker->word,
        "vatsim"     => $faker->randomDigit,
    ];
});

$factory->defineAs(App\Models\Mship\Qualification::class, 'atc', function ($faker) use ($factory) {
    $atc = $factory->raw(App\Models\Mship\Qualification::class);

    return array_merge($atc, [
        'code' => $faker->numerify("C##"),
        "type" => "atc",
    ]);
});

$factory->defineAs(App\Models\Mship\Qualification::class, 'pilot', function ($faker) use ($factory) {
    $atc = $factory->raw(App\Models\Mship\Qualification::class);

    return array_merge($atc, [
        'code' => $faker->numerify("P##"),
        "type" => "pilot",
    ]);
});

$factory->define(App\Models\Mship\Role::class, function ($faker) {
    return [
        "name"               => $faker->word,
        "session_timeout"    => $faker->numberBetween(100, 1000),
        "password_mandatory" => false,
        "password_lifetime"  => 0,
    ];
});