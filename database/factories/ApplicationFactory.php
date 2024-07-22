<?php

$factory->define(\App\Models\VisitTransferLegacy\Application::class, function ($faker) {
    return [
        'account_id' => \App\Models\Mship\Account::factory()->create()->id,
    ];
});

$factory->state(\App\Models\VisitTransferLegacy\Application::class, 'atc_visit', function ($faker) use ($factory) {
    $application = $factory->raw(\App\Models\VisitTransferLegacy\Application::class);

    return array_merge($application, [
        'type' => \App\Models\VisitTransferLegacy\Application::TYPE_VISIT,
        'facility_id' => factory(\App\Models\VisitTransferLegacy\Facility::class, 'atc_visit'),
        'training_team' => 'atc',
    ]);
});

$factory->state(\App\Models\VisitTransferLegacy\Application::class, 'atc_transfer', function ($faker) use ($factory) {
    $application = $factory->raw(\App\Models\VisitTransferLegacy\Application::class);

    return array_merge($application, [
        'type' => \App\Models\VisitTransferLegacy\Application::TYPE_TRANSFER,
        'facility_id' => factory(\App\Models\VisitTransferLegacy\Facility::class, 'atc_Transfer'),
        'training_team' => 'atc',
    ]);
});

$factory->state(\App\Models\VisitTransferLegacy\Application::class, 'pilot_visit', function ($faker) use ($factory) {
    $application = $factory->raw(\App\Models\VisitTransferLegacy\Application::class);

    return array_merge($application, [
        'type' => \App\Models\VisitTransferLegacy\Application::TYPE_VISIT,
        'facility_id' => factory(\App\Models\VisitTransferLegacy\Facility::class, 'pilot_visit'),
        'training_team' => 'pilot',
    ]);
});
