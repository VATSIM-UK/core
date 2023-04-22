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
    $qualAtc = Qualification::factory()->atc()->create();
    // Assoc qualification to account
    \DB::table('mship_account_qualification')->insert([
        'account_id' => $id,
        'qualification_id' => $qualAtc->id,
        'created_at' => \Carbon\Carbon::now(),
        'updated_at' => \Carbon\Carbon::now(),
    ]);

    $qualPilot = Qualification::factory()->pilot()->create();
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
