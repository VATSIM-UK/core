<?php

$factory->define(App\Models\Mship\Account::class, function (Faker\Generator $faker) {
    return [
        'id' => rand(10000000, 99999999),
        'name_first' => $faker->firstName,
        'name_last' => $faker->lastName,
        'email' => $faker->email,
        'is_invisible' => 0,
    ];
});

$factory->defineAs(App\Models\Mship\Account::class, 'withQualification', function (Faker\Generator $faker) {
    $id = rand(10000000, 99999999);
    $qual = factory(\App\Models\Mship\Qualification::class)->create();
    // Assoc qualification to account
    \DB::table('mship_account_qualification')->insert([
        'account_id' => $id,
        'qualification_id' => $qual->id,
        'created_at' => \Carbon\Carbon::now(),
        'updated_at' => \Carbon\Carbon::now(),
    ]);

    return [
        'id' => $id,
        'name_first' => $faker->firstName,
        'name_last' => $faker->lastName,
        'email' => $faker->email,
        'is_invisible' => 0,
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

$factory->define(\Spatie\Permission\Models\Role::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->word,
        'guard_name' => 'web',
        'session_timeout' => 180,
    ];
});

$factory->define(\Spatie\Permission\Models\Permission::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->word,
        'guard_name' => 'web',
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
