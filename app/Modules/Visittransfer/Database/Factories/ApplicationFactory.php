<?php

$factory->define(App\Modules\Visittransfer\Models\Application::class, function ($faker) {
    return [
        'account_id' => factory(\App\Models\Mship\Account::class)->create()->id,
    ];
});

$factory->defineAs(App\Modules\Visittransfer\Models\Application::class, 'atc_visit', function ($faker) use ($factory) {
    $application = $factory->raw(App\Modules\Visittransfer\Models\Application::class);

    return array_merge($application, [
        'type' => \App\Modules\Visittransfer\Models\Application::TYPE_VISIT,
        'training_team' => 'atc',
    ]);
});

$factory->defineAs(App\Modules\Visittransfer\Models\Application::class, 'atc_transfer', function ($faker) use ($factory) {
    $application = $factory->raw(App\Modules\Visittransfer\Models\Application::class);

    return array_merge($application, [
        'type' => \App\Modules\Visittransfer\Models\Application::TYPE_TRANSFER,
        'training_team' => 'atc',
    ]);
});

$factory->defineAs(App\Modules\Visittransfer\Models\Application::class, 'pilot_visit', function ($faker) use ($factory) {
    $application = $factory->raw(App\Modules\Visittransfer\Models\Application::class);

    return array_merge($application, [
        'type' => \App\Modules\Visittransfer\Models\Application::TYPE_VISIT,
        'training_team' => 'pilot',
    ]);
});
