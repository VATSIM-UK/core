<?php

Route::get('validations')->uses('Api\ValidationsController@view')->name('api.validations');
Route::get('metar/{airportIcao}')->uses('Site\MetarController@get')->name('api.metar');
Route::get('/cts/bookings')->uses('Api\CtsController@getBookings')->name('api.cts.bookings');

Route::group([
    'middleware' => 'api_auth',
], function () {
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
});
