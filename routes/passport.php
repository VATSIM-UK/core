<?php

use Laravel\Passport\Http\Controllers\AccessTokenController;
use Laravel\Passport\Http\Controllers\ApproveAuthorizationController;
use Laravel\Passport\Http\Controllers\AuthorizationController;
use Laravel\Passport\Http\Controllers\DenyAuthorizationController;
use Laravel\Passport\Http\Controllers\TransientTokenController;

Route::group([
    'as' => 'passport.',
    'prefix' => 'oauth',
], function () {
    Route::post('/token', [AccessTokenController::class, 'issueToken'])
        ->middleware('throttle')
        ->name('token');

    Route::get('/authorize', [AuthorizationController::class, 'authorize'])
        ->middleware('web')
        ->name('authorizations.authorize');

    Route::group(['middleware' => ['web', 'auth']], function () {
        Route::post('/authorize', [ApproveAuthorizationController::class, 'approve'])
            ->name('authorizations.approve');

        Route::delete('/authorize', [DenyAuthorizationController::class, 'deny'])
            ->name('authorizations.deny');

        Route::post('/token/refresh', [TransientTokenController::class, 'refresh'])
            ->name('token.refresh');
    });
});
