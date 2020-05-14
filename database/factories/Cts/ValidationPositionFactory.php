<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Cts\ValidationPosition::class, function (Faker $faker) {
    return [
        'position' => 'Heathrow (TWR)',
        'rts' => rand(1, 15),
        'min_rating' => rand(1, 12),
    ];
});
