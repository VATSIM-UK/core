<?php

Route::get('/community', function () {
    return Redirect::route('community.deploy');
});

Route::group([
    'as'         => 'community.admin.',
    'prefix'     => 'Admin',
    'namespace'  => 'Community\Adm',
    'domain'     => config('app.url'),
    'middleware' => ['auth.admin'],
], function () {
    Route::get('/', [
        'as'   => 'dashboard',
        'uses' => 'Dashboard@getDashboard',
    ]);
});

Route::group([
    'as'         => 'community.',
    'namespace'  => 'Site',
    'domain'     => config('app.url'),
    'prefix'     => 'community',
    'middleware' => ['auth.user.full', 'user.must.read.notifications'],
], function () {
    Route::group(['as' => 'membership.', 'prefix' => 'membership'], function () {
        Route::get('/deploy', [
            'as'   => 'deploy',
            'uses' => 'Membership@getDeploy',
        ]);

        Route::post('/deploy', [
            'as'   => 'deploy.post',
            'uses' => 'Membership@postDeploy',
        ]);
    });
});