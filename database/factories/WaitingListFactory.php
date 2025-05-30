<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Training\WaitingList::class, function (Faker $faker) {
    $name = $faker->name;

    return [
        'name' => $name,
        'slug' => str_slug($name),
        'department' => 'atc',
        'cts_theory_exam_level' => null,
        'feature_toggles' => [
            'check_atc_hours' => true,
        ],
        'requires_roster_membership' => false,
    ];
});

$factory->define(App\Models\Training\WaitingList\WaitingListStatus::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'retains_position' => true,
    ];
});

$factory->state(App\Models\Training\WaitingList\WaitingListStatus::class, 'default', [
    'default' => true,
]);

$factory->define(App\Models\Training\WaitingList\WaitingListFlag::class, function (Faker $faker) {
    return [
        'name' => $faker->name,
        'list_id' => factory(App\Models\Training\WaitingList::class),
        'default_value' => true,
    ];
});
