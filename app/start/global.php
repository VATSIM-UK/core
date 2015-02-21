<?php

/*
  |--------------------------------------------------------------------------
  | Register The Laravel Class Loader
  |--------------------------------------------------------------------------
  |
  | In addition to using Composer, you may use the Laravel class loader to
  | load your controllers and models. This is useful for keeping all of
  | your classes in the "global" namespace without Composer updating.
  |
 */

ClassLoader::addDirectories(array(
    app_path() . '/commands',
    app_path() . '/controllers',
    app_path() . '/models',
    app_path() . '/database/seeds',
    app_path() . '/enums',
));

/*
  |--------------------------------------------------------------------------
  | Application Error Logger
  |--------------------------------------------------------------------------
  |
  | Here we will configure the error logger setup for the application which
  | is built on top of the wonderful Monolog library. By default we will
  | build a basic log file setup which creates a single file for logs.
  |
 */

Log::useFiles(storage_path() . '/logs/laravel.log');

/*
  |--------------------------------------------------------------------------
  | Application Error Handler
  |--------------------------------------------------------------------------
  |
  | Here you may handle any errors that occur in your application, including
  | logging them or displaying custom views for specific errors. You may
  | even register several error handlers to handle different types of
  | exceptions. If nothing is returned, the default error view is
  | shown, which includes a detailed stack trace during debug.
  |
 */

class ValidationException extends Exception {

}

class AuthException extends Exception {

}

App::error(function(\AuthException $authException, $code) {
    // TODO: Log.
    return Redirect::to("/");
});
App::error(function(Exception $exception, $code) {
    foreach ($_ENV as $key => $value) {
        unset($_ENV[$key]);
        unset($_SERVER[$key]);
    }
    Log::error($exception->getFile().":".$exception->getFile().":".$exception->getLine()." --> ". $exception->getMessage());
});

App::missing(function($exception){
    // Is it an admin request?
    if(Request::is("adm*")){
        $request = Request::create(URL::route("adm.error", [404], false));
        return Route::dispatch($request);
    }
    throw $exception;
});

App::down(function() {
    return Response::make("Be right back!", 503);
});

/*
  |--------------------------------------------------------------------------
  | Require The Filters File
  |--------------------------------------------------------------------------
  |
  | Next we will load the filters file for the application. This gives us
  | a nice separate location to store our route and application filter
  | definitions instead of putting them all in the main routes file.
  |
 */

require app_path() . '/filters.php';

// Create VATUK_ACCOUNT_SYSTEM
if(!App::runningInConsole()){
    // We need to ensure that the VATSIM UK System accounts are in existance.
    define("VATUK_ACCOUNT_SYSTEM", "707070");

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