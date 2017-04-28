<?php

Route::get('/network-data', function () {
    return Redirect::route('networkdata.landing');
});

Route::group([
    'as' => 'networkdata.admin.',
    'namespace' => 'Admin',
    'domain' => config('app.url'),
    'prefix' => 'adm/network-data',
    'middleware' => ['auth.admin'],
], function () {
    Route::get('/', [
        'as' => 'dashboard',
        'uses' => 'Dashboard@getDashboard',
    ]);
});

Route::group([
    'as' => 'networkdata.',
    'namespace' => 'Site',
    'domain' => config('app.url'),
    'prefix' => 'network-data',
    'middleware' => ['auth_full_group'],
], function () {
    Route::get('/', function () {
        return redirect()->route('networkdata.online');
    })->name('landing');

    Route::get('/online', ['as' => 'online', 'uses' => 'Online@getOnline']);
});
