<?php

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| The first thing we will do is create a new Laravel application instance
| which serves as the "glue" for all the components of Laravel, and is
| the IoC container for the system binding all of the various parts.
|
*/

$app = new Illuminate\Foundation\Application(
    realpath(__DIR__.'/../')
);

/*
|--------------------------------------------------------------------------
| Bind Important Interfaces
|--------------------------------------------------------------------------
|
| Next, we need to bind some important interfaces into the container so
| we will be able to resolve them when needed. The kernels serve the
| incoming requests to this application from both the web and CLI.
|
*/

$app->singleton(
    Illuminate\Contracts\Http\Kernel::class,
    App\Http\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

/*
 * Custom housekeeping
 */

define("VATUK_ACCOUNT_SYSTEM", "707070");

// Ensure that the VATSIM UK System accounts are in existance.
// If this is not protected, we cannot run any artisan commands if there's an issue with the database.
if(!$app->runningInConsole()){
    $check = \Models\Mship\Account::find(VATUK_ACCOUNT_SYSTEM);
    if(!is_object($check) OR !$check->exists){
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

/*
|--------------------------------------------------------------------------
| Return The Application
|--------------------------------------------------------------------------
|
| This script returns the application instance. The instance is given to
| the calling script so we can separate the building of the instances
| from the actual running of the application and sending responses.
|
*/

return $app;
