<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Training\WaitingList::class, function (Faker $faker) {
    $name = $faker->name;

    return [
        'name' => $name,
        'slug' => str_slug($name),
    ];
});

$factory->define(App\Models\Training\WaitingListStatus::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'retains_position' => true,
    ];
});

$factory->state(App\Models\Training\WaitingListStatus::class, 'default', [
    'default' => true,
]);

$factory->define(\App\Models\Training\WaitingListFlag::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'default_value' => true,
    ];
});
