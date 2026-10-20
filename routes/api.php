<?php

use App\Http\Controllers\Bookings\CalendarFeedController;

Route::get('validations')->uses('Api\ValidationsController@view')->name('api.validations');
Route::get('metar/{airportIcao}')->uses('Site\MetarController@get')->name('api.metar');
Route::get('/bookings')->uses('Api\CtsController@getBookings')->name('api.cts.bookings');
Route::get('trackaudio-latest')->uses('Api\TrackAudioController@getLatest')->name('api.trackaudio.latest');

// Read-only iCal feed; the URL token is the credential, as clients cannot authenticate.
Route::get('calendar/feed/{token}.ics', CalendarFeedController::class)
    ->where('token', '[A-Za-z0-9]{48}')
    ->name('api.bookings.calendar.feed');

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
