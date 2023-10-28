<?php

// Admin
Route::group([
    'prefix' => 'adm',
    'namespace' => 'Adm',
    'middleware' => ['auth_full_group', 'admin'],
    'as' => 'adm.',
], function () {
    // Main
    Route::get('/')->uses('Dashboard@index')->name('index');

    // smartCARS
    Route::group([
        'prefix' => 'smartcars',
        'namespace' => 'Smartcars\Resources',
        'as' => 'smartcars.',
    ], function () {
        Route::resource('configure/aircraft', 'AircraftController')->except('show');
        Route::resource('configure/airports', 'AirportController')->except('show');
        Route::resource('configure/exercises', 'ExerciseController')->except('show');
        Route::resource('exercises.resources', 'ExerciseResourceController')->except('show');
        Route::resource('flights', 'FlightController')->only('index', 'edit', 'update');
    });

    // Visiting/Transferring
    Route::group([
        'as' => 'visiting.',
        'prefix' => 'visit-transfer',
        'namespace' => 'VisitTransfer',
    ], function () {
        Route::get('/')->uses('Dashboard@getDashboard')->name('dashboard');
        Route::get('/facility')->uses('Facility@getList')->name('facility');
        Route::get('/facility/create')->uses('Facility@getCreate')->name('facility.create');
        Route::post('/facility/create')->uses('Facility@postCreate')->name('facility.create.post');
        Route::get('/facility/{facility}/update')->where('facility', "\d+")->uses('Facility@getUpdate')->name('facility.update');
        Route::post('/facility/{facility}/update')->where('facility', "\d+")->uses('Facility@postUpdate')->name('facility.update.post');
        Route::get('/reference/{reference}')->where('reference', "\d+")->uses('Reference@getView')->name('reference.view');
        Route::post('/reference/{reference}/reject')->where('reference', "\d+")->uses('Reference@postReject')->name('reference.reject.post');
        Route::post('/reference/{reference}/accept')->where('reference', "\d+")->uses('Reference@postAccept')->name('reference.accept.post');
        Route::get('/reference/{scope?}')->where('scope', '[a-zA-Z-]+')->uses('Reference@getList')->name('reference.list');
        Route::get('/application/{application}')->where('application', "\d+")->uses('Application@getView')->name('application.view');
        Route::post('/application/{application}/check/met')->uses('Application@postCheckMet')->name('application.check.met.post');
        Route::post('/application/{application}/check/not-met')->uses('Application@postCheckNotMet')->name('application.check.notmet.post');
        Route::post('/application/{application}/setting/toggle')->uses('Application@postSettingToggle')->name('application.setting.toggle.post');
        Route::post('/application/{application}/accept')->where('application', "\d+")->uses('Application@postAccept')->name('application.accept.post');
        Route::post('/application/{application}/reject')->where('application', "\d+")->uses('Application@postReject')->name('application.reject.post');
        Route::post('/application/{application}/complete')->where('application', "\d+")->uses('Application@postComplete')->name('application.complete.post');
        Route::post('/application/{application}/cancel')->where('application', "\d+")->uses('Application@postCancel')->name('application.cancel.post');
        Route::get('/application/{scope?}')->where('scope', "\w+")->uses('Application@getList')->name('application.list');
        Route::get('/hours/')->uses('VisitorStatsController@create')->name('hours.create');
        Route::get('/hours/search')->uses('VisitorStatsController@index')->name('hours.search');
    });
});
