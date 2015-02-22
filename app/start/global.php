<?php

ClassLoader::addDirectories(array(
    app_path() . '/commands',
    app_path() . '/controllers',
    app_path() . '/models',
    app_path() . '/database/seeds',
    app_path() . '/enums',
));

Cache::extend('fcache', function($app)
{
    $store = new Artdevue\Fcache\Fcache;
    return new Illuminate\Cache\Repository($store);
});

Log::useFiles(storage_path() . '/logs/laravel.log');


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

    // Is it an admin request?
    if(Request::is("adm*")){
        Session::flash("error_message", $exception->getMessage());
        Session::flash("error_line", $exception->getLine());
        Session::flash("error_file", $exception->getFile());
        $request = Request::create(URL::route("adm.error", [500], false));
        return Route::dispatch($request);
    } else {
        Session::flash("error_message", $exception->getMessage());
        Session::flash("error_line", $exception->getLine());
        Session::flash("error_file", $exception->getFile());
        $request = Request::create(URL::route("error", [500], false));
        return Route::dispatch($request);
    }
});

App::missing(function($exception){
    // Is it an admin request?
    if(Request::is("adm*")){
        $request = Request::create(URL::route("adm.error", [404], false));
        return Route::dispatch($request);
    } else {
        $request = Request::create(URL::route("error", [404], false));
        return Route::dispatch($request);
    }
});

App::down(function() {
    return Response::make("Be right back!", 503);
});

require app_path() . '/filters.php';

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