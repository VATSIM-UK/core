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
        'token' => env('SLACK_SECRET', 'secret'),
        'token_register' => env('SLACK_TOKEN_REGISTER', 'token'),
    ],

    'teamspeak' => [
        'host' => env('TS_HOST'),
        'username' => env('TS_USER'),
        'password' => env('TS_PASS'),
        'port' => env('TS_PORT'),
        'query_port' => env('TS_QUERY_PORT'),
    ],

    'google' => [
        'maps' => [
            'jsapi' => env('MAPS_API_KEY', ''),
        ],
    ],

    'ukcp' => [
        'url' => env('UKCP_URL', 'https://ukcp.vatsim.uk'),
        'key' => env('UKCP_KEY'),
    ],

    'chartfox' => [
        'private_token' => env('CHARTFOX_PRIVATE_TOKEN'),
        'public_token' => env('CHARTFOX_PUBLIC_TOKEN'),
    ],

    'autotools' => [
        'division' => env('VATSIM_AT_DIV'),
        'username' => env('VATSIM_CERT_AT_USER'),
        'password' => env('VATSIM_CERT_AT_PASS'),
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
