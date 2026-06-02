<?php

/**
 * Laravel Fortify — two-factor authentication only.
 *
 * VATSIM OAuth and secondary passwords remain the application's login flow.
 * Fortify's default routes are disabled; 2FA HTTP endpoints live in
 * routes/fortify-two-factor.php and App\Providers\FortifyServiceProvider.
 */

use Laravel\Fortify\Features;

return [

    /*
    |--------------------------------------------------------------------------
    | Guard
    |--------------------------------------------------------------------------
    |
    | Session guard used for 2FA challenge completion and authenticated 2FA
    | management actions (enable, confirm, recovery codes).
    |
    */

    'guard' => 'web',

    /*
    |--------------------------------------------------------------------------
    | TOTP account label
    |--------------------------------------------------------------------------
    |
    | Attribute on Account used as the account name in authenticator QR codes.
    |
    */

    'username' => 'email',

    'email' => 'email',

    /*
    |--------------------------------------------------------------------------
    | Redirects
    |--------------------------------------------------------------------------
    |
    | Default intended URL after a successful 2FA challenge (custom response
    | classes may override). Not used for login or password reset.
    |
    */

    'home' => '/mship/manage/dashboard',

    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    |
    | Applied to routes in routes/fortify-two-factor.php.
    |
    */

    'middleware' => ['web'],

    /*
    |--------------------------------------------------------------------------
    | Rate limiting
    |--------------------------------------------------------------------------
    |
    | Only the two-factor challenge limiter is registered (see
    | App\Providers\FortifyServiceProvider).
    |
    */

    'limiters' => [
        'two-factor' => 'two-factor',
    ],

    /*
    |--------------------------------------------------------------------------
    | Features
    |--------------------------------------------------------------------------
    |
    | Do not add registration, resetPasswords, updatePasswords, passkeys, etc.
    | confirmPassword gates enable/disable behind secondary-password confirmation.
    |
    */

    'features' => [
        Features::twoFactorAuthentication([
            'confirm' => true,
            'confirmPassword' => true,
        ]),
    ],

];
