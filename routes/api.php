<?php

Route::get('user')->uses('Api\OAuthUserController@view');

// NETWORK DATA

Route::group([
    'as' => 'networkdata.api.',
    'namespace' => 'NetworkData',
    'domain' => config('app.url'),
    'prefix' => 'network-data',
], function () {
    Route::get('/online', [
        'as' => 'online',
        'uses' => 'Feed@getOnline',
    ]);
});

// SMARTCARS

Route::group([
    'as' => 'smartcars.api.',
    'prefix' => 'smartcars',
    'namespace' => 'Smartcars\Api',
    'domain' => config('app.url'),
], function () {
    Route::get('/call', [
        'as' => 'call',
        'uses' => 'Router@getRoute',
    ]);

    Route::post('/call', [
        'as' => 'call.post',
        'uses' => 'Router@postRoute',
    ]);

    Route::group(['as' => 'auth.', 'prefix' => 'auth/'], function () {
        Route::post('/manual', [
            'as' => 'manual',
            'uses' => 'Authentication@postManual',
        ]);

        Route::post('/auto', [
            'as' => 'auto',
            'uses' => 'Authentication@postAuto',
        ]);

        Route::post('/verify', [
            'as' => 'verify',
            'uses' => 'Authentication@postVerify',
        ]);
    });
});
