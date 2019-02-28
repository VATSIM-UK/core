<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Stripe, Mailgun, SparkPost and others. This file provides a sane
    | default location for this type of information, allowing packages
    | to have a conventional place to find your various credentials.
    |
    */

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
    ],

    'ses' => [
        'key' => env('SES_KEY'),
        'secret' => env('SES_SECRET'),
        'region' => 'us-east-1',
    ],

    'sparkpost' => [
        'secret' => env('SPARKPOST_SECRET'),
    ],

    'stripe' => [
        'model' => App\Models\Mship\Account::class,
        'key' => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],

    'slack' => [
        'token' => env('SLACK_SECRET'),
    ],

    'google' => [
        'maps' => [
            'jsapi' => env('MAPS_API_KEY', ''),
        ],
    ],

    'ukcp' => [
        'url' => env('UKCP_URL'),
        'key' => env('UKCP_KEY'),
    ],

    'chartfox' => [
        'private_token' => env('CHARTFOX_PRIVATE_TOKEN'),
        'public_token' => env('CHARTFOX_PUBLIC_TOKEN'),
    ],

    /*
    |--------------------------------------------------------------------------
    | VATSIM UK Hosted Services
    |--------------------------------------------------------------------------
    */

    'community' => [
        'database' => env('COMMUNITY_DATABASE'),
        'init_file' => env('COMMUNITY_INIT_FILE'),
    ],

    'cts' => [
        'database' => env('CTS_DATABASE'),
    ],

    'helpdesk' => [
        'database' => env('HELPDESK_DATABASE'),
    ],

    'moodle' => [
        'database' => env('MOODLE_DATABASE'),
    ],

];
