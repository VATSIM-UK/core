<?php

$factory->define(App\Models\Sys\Notification::class, function ($faker) {
    return [
        'title' => $faker->sentence,
        'content' => $faker->paragraph,
        'status' => \App\Models\Sys\Notification::STATUS_GENERAL,
        'effective_at' => \Carbon\Carbon::now(),
    ];
});

$factory->defineAs(App\Models\Sys\Notification::class, 'must_read', function ($faker) use ($factory) {
    $raw = $factory->raw(App\Models\Sys\Notification::class);

    return array_merge($raw, [
        'status' => \App\Models\Sys\Notification::STATUS_MUST_ACKNOWLEDGE,
        'effective_at' => \Carbon\Carbon::now(),
    ]);
});

$factory->defineAs(App\Models\Sys\Notification::class, 'important', function ($faker) use ($factory) {
    $raw = $factory->raw(App\Models\Sys\Notification::class);

    return array_merge($raw, [
        'status' => \App\Models\Sys\Notification::STATUS_IMPORTANT,
        'effective_at' => \Carbon\Carbon::now(),
    ]);
});
