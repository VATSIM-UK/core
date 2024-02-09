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

    'teamspeak' => [
        'host' => env('TS_HOST'),
        'username' => env('TS_USER'),
        'password' => env('TS_PASS'),
        'port' => env('TS_PORT'),
        'query_port' => env('TS_QUERY_PORT'),
    ],

    'discord' => [
        'guild_id' => env('DISCORD_GUILD_ID', null),
        'token' => env('DISCORD_TOKEN', null),
        'client_id' => env('DISCORD_CLIENT', null),
        'client_secret' => env('DISCORD_SECRET', null),
        'redirect_uri' => env('DISCORD_REDIRECT_URI', null),
        'base_discord_uri' => env('DISCORD_API_BASE', 'https://discord.com/api/v6'),
        'suspended_member_role_id' => env('DISCORD_SUSPENDED_MEMBER_ROLE_ID', null),
        'training_alerts_channel_id' => env('DISCORD_TRAINING_ALERTS_CHANNEL_ID', null),
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
    ],

    'cts' => [
        'database' => env('CTS_DATABASE'),
    ],

    'helpdesk' => [
        'database' => env('HELPDESK_DATABASE'),
    ],

    'moodle' => [
        'database' => env('MOODLE_DATABASE'),
        'oauth_issuer_id' => env('MOODLE_OAUTH_ISSUER_ID'),
    ],

];
