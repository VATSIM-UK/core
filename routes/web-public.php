<?php
    Route::get('/airports/{ukAirportByICAO}')->uses('Airport\ViewAirportController@show')->name('airport.view');