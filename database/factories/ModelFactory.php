<?php

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
