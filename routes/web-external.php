<?php

use App\Http\Controllers\External\VatsimNet\ProcessVatsimNetWebhook;

Route::group([
    'prefix' => 'external',
    'as' => 'external.',
], function () {

    Route::group([
        'prefix' => 'vatsim-net',
        'as' => 'vatsim-net.',
    ], function () {
        Route::post('webhook', ProcessVatsimNetWebhook::class)->middleware('throttle:60,1')->name('webhook');
    });

});
