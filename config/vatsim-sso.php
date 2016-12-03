<?php
/*
 * DO NOT PUBLISH THE KEY, SECRET AND CERT TO CODE REPOSITORIES
 * FOR SECURITY. PLEASE USE LARAVEL'S .envFILES TO PROTECT
 * SENSITIVE DATA.
 * http://laravel.com/docs/5.0/configuration#environment-configuration
 *
 * Some sensible defaults have been provided so you can use .env files by adding
 * `SSO_KEY`, `SSO_SECRET`, and `SSO_CERT` to your `.env` (production).
 *
 * NOTE THAT THE `SSO_CERT` MUST BE ON ONE LINE IN `.env`: use `SSO_CERT="[private key]"`, replace line breaks with `\n`
 *
 * Modify the three constants below to match the keys in your .env, otherwise it will use what you enter
 * on the second line of the key/secret/cert elements
 */

return [

    /*
     * The location of the VATSIM OAuth interface
     */
    'base' => 'https://cert.vatsim.net/sso/',

    /*
     * The consumer key for your organisation (provided by VATSIM)
     */
    'key' => env('SSO_KEY', ''),

    /*
    * The secret key for your organisation (provided by VATSIM)
    * Do not give this to anyone else or display it to your users. It must be kept server-side
    */
    'secret' => env('SSO_SECRET', ''),

    /*
     * The URL users will be redirected to after they log in, this should
     * be on the same server as the request
     */
    'return' => '', //not sensitive

    /*
     * The signing method you are using to encrypt your request signature.
     * Different options must be enabled on your account at VATSIM.
     * Options: RSA / HMAC
     */
    'method' => 'RSA',

    /*
     * Your RSA **PRIVATE** key
     * If you are not using RSA, this value can be anything (or not set)
     */
    'cert' => str_replace('\n', "\n", env('SSO_CERT', '')),
];
