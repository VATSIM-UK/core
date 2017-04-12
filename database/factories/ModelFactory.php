<?php

/**
 * @var Illuminate\Database\Eloquent\Factory
 */
foreach (Module::enabled() as $module) {
    $factory->load('app/Modules/'.$module['basename'].'/Database/Factories');
}

$factory->define(App\Models\Mship\Account::class, function (Faker\Generator $faker) {
    return [
        'id' => rand(10000000, 99999999),
        'name_first' => $faker->name,
        'name_last' => $faker->name,
        'email' => $faker->email,
        'is_invisible' => 0,
    ];
});

$factory->define(App\Models\Mship\Account\Email::class, function (Faker\Generator $faker) {
    return [
        'id' => $faker->numberBetween(1, 100000),
        'account_id' => factory(\App\Models\Mship\Account::class)->create()->id,
        'email' => $faker->email,
        'verified_at' => $faker->dateTime(),
        'created_at' => $faker->dateTime(),
        'updated_at' => $faker->dateTime(),
    ];
});

$factory->define(App\Models\Mship\Qualification::class, function (Faker\Generator $faker) {
    return [
        'code' => $faker->bothify('?##'),
        'name_small' => $faker->word,
        'name_long' => $faker->word,
        'name_grp' => $faker->word,
        'vatsim' => $faker->randomDigit,
    ];
});

$factory->defineAs(App\Models\Mship\Qualification::class, 'atc', function (Faker\Generator $faker) use ($factory) {
    $atc = $factory->raw(App\Models\Mship\Qualification::class);

    return array_merge($atc, [
        'code' => $faker->numerify('C##'),
        'type' => 'atc',
    ]);
});

$factory->defineAs(App\Models\Mship\Qualification::class, 'pilot', function (Faker\Generator $faker) use ($factory) {
    $atc = $factory->raw(App\Models\Mship\Qualification::class);

    return array_merge($atc, [
        'code' => $faker->numerify('P##'),
        'type' => 'pilot',
    ]);
});

$factory->define(App\Models\Mship\Role::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->word,
        'session_timeout' => $faker->numberBetween(100, 1000),
        'password_mandatory' => false,
        'password_lifetime' => 0,
    ];
});

$factory->define(App\Models\Mship\Permission::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->word,
        'display_name' => $faker->sentence,
    ];
});

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

$factory->define(\App\Models\Mship\Permission::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->regexify('([A-Z0-9._ ]{1,10}\/){2}testpermission'),
        'display_name' => $faker->text($maxNbChars = 30),
    ];
});

$factory->define(App\Models\Messages\Thread::class, function (Faker\Generator $faker) {
    return [
        'subject' => $faker->text($maxNbChars = 255),
    ];
});

$factory->define(App\Models\Messages\Thread\Post::class, function (Faker\Generator $faker) {
    return [
        'account_id' => factory(App\Models\Mship\Account::class)->create()->id,
        'content' => $faker->text($maxNbChars = 255),
    ];
});
