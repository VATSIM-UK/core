<?php

use App\Models\Mship\Qualification;

$factory->define(App\Models\Mship\Account::class, function (Faker\Generator $faker) {
    return [
        'id' => rand(10000000, 99999999),
        'name_first' => $faker->firstName,
        'name_last' => $faker->lastName,
        'email' => $faker->email,
        'is_invisible' => 0,
    ];
});

$factory->state(App\Models\Mship\Account::class, 'withQualification', function (Faker\Generator $faker) {
    $id = rand(10000000, 99999999);
    $qualAtc = factory(Qualification::class)->states('atc')->create();
    // Assoc qualification to account
    \DB::table('mship_account_qualification')->insert([
        'account_id' => $id,
        'qualification_id' => $qualAtc->id,
        'created_at' => \Carbon\Carbon::now(),
        'updated_at' => \Carbon\Carbon::now(),
    ]);

    $qualPilot = factory(Qualification::class)->states('pilot')->create();
    // Assoc qualification to account
    \DB::table('mship_account_qualification')->insert([
        'account_id' => $id,
        'qualification_id' => $qualPilot->id,
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
    $foundUniqueCode = false;
    while (!$foundUniqueCode) {
        $code = $faker->bothify('?##');
        if (!Qualification::code($code)->exists()) {
            $foundUniqueCode = true;
        }
    }

    return [
        'code' => $code,
        'name_small' => $faker->word,
        'name_long' => $faker->word,
        'name_grp' => $faker->word,
        'vatsim' => $faker->randomDigit,
    ];
});

$factory->state(App\Models\Mship\Qualification::class, 'atc', function (Faker\Generator $faker) use ($factory) {
    $atc = $factory->raw(App\Models\Mship\Qualification::class);

    return array_merge($atc, [
        'code' => $faker->numerify('C##'),
        'type' => 'atc',
    ]);
});

$factory->state(App\Models\Mship\Qualification::class, 'pilot', function (Faker\Generator $faker) use ($factory) {
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
