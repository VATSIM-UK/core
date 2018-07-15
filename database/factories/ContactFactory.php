<?php

use Faker\Generator as Faker;

$factory->define(\App\Models\Contact::class, function (Faker $faker) {
    return [
        'key' => $faker->word,
        'name' => $faker->words(2),
        'email' => $faker->email,
    ];
});