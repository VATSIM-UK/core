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
            Log::info(print_r([
                'Authorization' => request()->header('Authorization'),
                'User-Agent' => request()->header('User-Agent'),
                'Body' => request()->all(),
            ], true));

            return response()->json([
                'status' => 'ok',
            ]);
        })->name('webhook');

    });

});
