<?php

$factory->define(\App\Models\Atc\Endorsement::class, function ($faker) {
    return [
        'name' => $faker->name,
    ];
});

$factory->define(\App\Models\Atc\Endorsement\Condition::class, function ($faker) {
    return [
        'endorsement_id' => function () {
            return factory(\App\Models\Atc\Endorsement::class)->create()->id;
        },
        'positions' => ['EGLL_%'],
        'required_hours' => $faker->numberBetween(1, 100),
        'type' => 1,
        'within_months' => $faker->optional(0.5)->randomDigit,
    ];
});
