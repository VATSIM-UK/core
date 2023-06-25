<?php

$factory->define(App\Models\TeamSpeak\Channel::class, function (Faker\Generator $faker) {
    return [
        'id' => $faker->numberBetween(1, 65535),
        'name' => $faker->text($maxNbChars = 30),
    ];
});

$factory->define(\App\Models\TeamSpeak\ServerGroup::class, function (Faker\Generator $faker) {
    return [
        'dbid' => $faker->numberBetween(1, 65535),
        'name' => $faker->text($maxNbChars = 30),
        'type' => 's',
    ];
});

$factory->define(\App\Models\TeamSpeak\ChannelGroup::class, function (Faker\Generator $faker) {
    return [
        'dbid' => $faker->numberBetween(1, 65535),
        'name' => $faker->text($maxNbChars = 30),
        'type' => 'c',
    ];
});

$factory->define(\App\Models\TeamSpeak\Registration::class, function (Faker\Generator $faker) {
    return [
        'account_id' => function () {
            return \App\Models\Mship\Account::factory()->create();
        },
        'registration_ip' => $faker->ipv4,
        'last_ip' => $faker->ipv4,
        'last_login' => $faker->dateTime,
        'last_os' => $faker->randomElement(['Windows', 'OSX', 'Linux', 'iOS', 'Android']),
        'created_at' => \Carbon\Carbon::now(),
        'updated_at' => \Carbon\Carbon::now(),
    ];
});
