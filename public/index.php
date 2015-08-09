<?php

/**
 * Laravel - A PHP Framework For Web Artisans
 *
 * @package  Laravel
 * @author   Taylor Otwell <taylorotwell@gmail.com>
 */

/*
|--------------------------------------------------------------------------
| Register The Auto Loader
|--------------------------------------------------------------------------
|
| Composer provides a convenient, automatically generated class loader for
| our application. We just need to utilize it! We'll simply require it
| into the script here so that we don't have to worry about manual
| loading any of our classes later on. It feels nice to relax.
|
*/

require __DIR__.'/../bootstrap/autoload.php';

/*
|--------------------------------------------------------------------------
| Turn On The Lights
|--------------------------------------------------------------------------
|
| We need to illuminate PHP development, so let us turn on the lights.
| This bootstraps the framework and gets it ready for use, then it
| will load up this application so that we can run it and send
| the responses back to the browser and delight our users.
|
*/

$app = require_once __DIR__.'/../bootstrap/app.php';

/*
|--------------------------------------------------------------------------
| Run The Application
|--------------------------------------------------------------------------
|
| Once we have the application, we can handle the incoming request
| through the kernel, and send the associated response back to
| the client's browser allowing them to enjoy the creative
| and wonderful application we have prepared for them.
|
*/

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

define("VATUK_ACCOUNT_SYSTEM", "707070");

// Ensure that the VATSIM UK System accounts are in existence.
// If this is not protected, we cannot run any artisan commands if there's an issue with the database.
if(!$app->runningInConsole()){
    $check = \Models\Mship\Account::find(VATUK_ACCOUNT_SYSTEM);
    if(!is_object($check) || !$check->exists){
        $a = new \Models\Mship\Account();
        $a->account_id = VATUK_ACCOUNT_SYSTEM;
        $a->name_first = "VATSIM";
        $a->name_last = "UK";
        $a->is_system = true;
        $a->save();

        // Add all required emails by this account.
        $a->addEmail("no-reply@vatsim-uk.co.uk", true, true);
    }
}

$response->send();

$kernel->terminate($request, $response);
