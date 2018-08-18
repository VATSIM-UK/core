<?php

    Route::get('/airports')->uses('Airport\ViewAirportController@index')->name('airports');
    Route::get('/airports/{ukAirportByICAO}')->uses('Airport\ViewAirportController@show')->name('airport.view');

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
