<?php

Route::group([
    'as' => 'site.',
    'namespace' => 'Site',
], function () {
    Route::get('/')->uses('HomePageController')->name('home');
    Route::get('/join')->uses('JoinPageController')->name('join');
    Route::get('/staff')->uses('StaffPageController')->name('staff');
    Route::get('/airports')->uses('AirportController@index')->name('airports');
    Route::get('/airports/{ukAirportByICAO}')->uses('AirportController@show')->name('airport.view');

    Route::group([
           'as' => 'atc.',
           'prefix' => 'atc',
        ], function () {
            Route::get('/')->uses('ATCPagesController@viewLanding')->name('landing');
            Route::get('/new-controller')->uses('ATCPagesController@viewNewController')->name('newController');
            Route::get('/progression-guide')->uses('ATCPagesController@viewProgressionGuide')->name('progression');
            Route::get('/endorsements')->uses('ATCPagesController@viewEndorsements')->name('endorsements');
            Route::get('/becoming-a-mentor')->uses('ATCPagesController@viewBecomingAMentor')->name('mentor');
            Route::get('/bookings')->uses('ATCPagesController@viewBookings')->name('bookings');
        });
});

// Helpers
Route::get('metar/{airportIcao}', function ($airportIcao) {
    return Cache::remember("vatsim.metar.$airportIcao", 5, function () use ($airportIcao) {
        $client = new GuzzleHttp\Client();

        try {
            $response = $client->get("http://metar.vatsim.net/metar.php?id=$airportIcao");

            if ($response->getStatusCode() === 200) {
                return (string) $response->getBody();
            }
        } catch (GuzzleHttp\Exception\TransferException $e) {
        }

        return 'METAR UNAVAILABLE';
    });
})->name('metar');
