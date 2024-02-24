<?php

Route::group([
    'prefix' => 'external',
    'as' => 'external.',
], function () {

    Route::group([
        'prefix' => 'vatsim-net',
        'as' => 'vatsim-net.',
    ], function () {

        Route::post('webhook', function () {
            return response()->json([
                'status' => 'ok',
            ]);
        })->name('webhook');

    });

});
