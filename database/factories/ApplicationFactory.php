<?php

$factory->define(\App\Models\VisitTransfer\Application::class, function ($faker) {
    return [
        'account_id' => factory(\App\Models\Mship\Account::class)->create()->id,
    ];
});

$factory->state(\App\Models\VisitTransfer\Application::class, 'atc_visit', function ($faker) use ($factory) {
    $application = $factory->raw(\App\Models\VisitTransfer\Application::class);

    return array_merge($application, [
        'type' => \App\Models\VisitTransfer\Application::TYPE_VISIT,
        'training_team' => 'atc',
    ]);
});

$factory->state(\App\Models\VisitTransfer\Application::class, 'atc_transfer', function ($faker) use ($factory) {
    $application = $factory->raw(\App\Models\VisitTransfer\Application::class);

    return array_merge($application, [
        'type' => \App\Models\VisitTransfer\Application::TYPE_TRANSFER,
        'training_team' => 'atc',
    ]);
});

$factory->state(\App\Models\VisitTransfer\Application::class, 'pilot_visit', function ($faker) use ($factory) {
    $application = $factory->raw(\App\Models\VisitTransfer\Application::class);

    return array_merge($application, [
        'type' => \App\Models\VisitTransfer\Application::TYPE_VISIT,
        'training_team' => 'pilot',
    ]);
});
