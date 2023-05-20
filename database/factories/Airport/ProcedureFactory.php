<?php

use Faker\Generator as Faker;

$factory->define(\App\Models\Airport\Procedure::class, function (Faker $faker) {
    $airport = factory(\App\Models\Airport::class)->create();

    return [
        'airport_id' => function () use ($airport) {
            return $airport->id;
        },
        'runway_id' => function () use ($airport) {
            return factory(\App\Models\Airport\Runway::class)->create(['airport_id' => $airport->id])->id;
        },
        'type' => $faker->randomElement([1, 2]),
        'ident' => strtoupper($faker->word).$faker->numberBetween(0, 100),
        'initial_fix' => $faker->optional(0.5)->passthrough(strtoupper($faker->word)),
        'initial_altitude' => $faker->optional(0.5)->numberBetween(100, 10000),
        'final_altitude' => $faker->optional(0.5)->numberBetween(100, 10000),
        'remarks' => $faker->optional(0.5)->text(),
    ];
});
