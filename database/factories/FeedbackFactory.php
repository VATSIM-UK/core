<?php

$factory->define(App\Models\Mship\Feedback\Form::class, function (Faker\Generator $faker) {
    return [
        'name' => $faker->words(2),
        'slug' => strtolower($faker->word),
        'contact_id' => $faker->numberBetween(1,3),
        'enabled' => 1,
        'targeted' => 1,
        'public' => 1,
    ];
});