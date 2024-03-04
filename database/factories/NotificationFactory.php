<?php

use Faker\Generator as Faker;

$factory->define(\App\Models\Sys\Notification::class, function (Faker $faker) {
    return [
        'title' => $faker->word,
        'content' => $faker->text,
        'status' => \App\Models\Sys\Notification::STATUS_GENERAL,
        'created_at' => \Carbon\Carbon::now()->subMinute(),
        'updated_at' => \Carbon\Carbon::now()->subMinute(),
        'effective_at' => \Carbon\Carbon::now()->subMinute(),
    ];
});
