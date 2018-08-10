<?php
    Route::get('/airports')->uses('Airport\ViewAirportController@index')->name('airports');
    Route::get('/airports/{ukAirportByICAO}')->uses('Airport\ViewAirportController@show')->name('airport.view');
