<?php

use Faker\Generator as Faker;

$factory->define(\App\Models\Airport\Navaid::class, function (Faker $faker) {
    return [
        'airport_id' => function () {
            return factory(\App\Models\Airport::class)->create()->id;
        },
        'type' => $faker->numberBetween(1, 6),
        'name' => $faker->optional(0.5)->passthrough('RWY' . $faker->numberBetween(1, 36)),
        'heading' => $faker->optional(0.5)->numberBetween(0, 360),
        'ident' => $faker->randomLetter . $faker->randomLetter . $faker->randomLetter . $faker->randomLetter,
        'frequency' => $faker->randomFloat(3, 0, 600),
        'frequency_band' => $faker->randomElement([1, 2]),
        'remarks' => $faker->optional(0.5)->text(50),
    ];
});
