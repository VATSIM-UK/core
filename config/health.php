<?php

use Spatie\Health\ResultStores\InMemoryHealthResultStore;

return [
    // Checks run live in the /health request; no results are persisted.
    'result_stores' => [
        InMemoryHealthResultStore::class => [],
    ],

    // Alerting is done by the external uptime monitor, not by the app.
    'notifications' => [
        'enabled' => false,
    ],
];
