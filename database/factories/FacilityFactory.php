<?php

$factory->define(\App\Models\VisitTransfer\Facility::class, function ($faker) {
    return [
        'name' => $faker->name,
        'description' => $faker->paragraph,
    ];
});

$factory->defineAs(\App\Models\VisitTransfer\Facility::class, 'atc_visit', function ($faker) use ($factory) {
    $facility = $factory->raw(\App\Models\VisitTransfer\Facility::class);

    return array_merge($facility, [
        'can_visit' => true,
        'training_team' => 'atc',
    ]);
});
