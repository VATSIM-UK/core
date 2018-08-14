<?php

use Faker\Generator as Faker;

$factory->define(\App\Models\Airport\Runway::class, function (Faker $faker) {
    return [
        'airport_id' => function () {
            return factory(\App\Models\Airport::class)->create()->id;
        },
        'ident' => $faker->numberBetween(0, 3).$faker->numberBetween(0, 9).$faker->optional(0.5)->randomLetter,
        'heading' => $faker->numberBetween(0, 360),
        'width' => $faker->numberBetween(0, 100),
        'length' => $faker->numberBetween(400, 5000),
        'surface_type' => $faker->numberBetween(1, 5),
    ];
});
