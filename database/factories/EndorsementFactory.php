<?php

$factory->define(\App\Models\Atc\PositionGroup::class, function ($faker) {
    return [
        'name' => $faker->name,
    ];
});

$factory->define(\App\Models\Atc\PositionGroupCondition::class, function ($faker) {
    return [
        'position_group_id' => function () {
            return factory(\App\Models\Atc\PositionGroup::class)->create()->id;
        },
        'positions' => ['EGLL_%'],
        'required_hours' => $faker->numberBetween(1, 100),
        'type' => 1,
        'within_months' => $faker->optional(0.5)->randomDigit,
        'required_qualification' => null,
    ];
});
