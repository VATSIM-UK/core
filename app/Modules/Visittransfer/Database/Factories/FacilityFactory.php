<?php

$factory->define(App\Modules\Visittransfer\Models\Facility::class, function ($faker) {
    return [
        'name' => $faker->name,
        'description' => $faker->paragraph,
    ];
});

$factory->defineAs(App\Modules\Visittransfer\Models\Facility::class, 'atc_visit', function ($faker) use ($factory) {
    $facility = $factory->raw(App\Modules\Visittransfer\Models\Facility::class);

    return array_merge($facility, [
        'can_visit' => true,
        'training_team' => 'atc',
    ]);
});
