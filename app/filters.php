<?php

/*
|--------------------------------------------------------------------------
| Application & Route Filters
|--------------------------------------------------------------------------
|
| Below you will find the "before" and "after" events for the application
| which may be used to do any work before or after a request into your
| application. Here you may also register your custom route filters.
|
*/

App::before(function($request)
{

});


App::after(function($request, $response)
{
	//
});

Route::filter('auth.user.basic', function() {
    if (!Session::get("auth_basic", false)) {
        if (Request::ajax()) {
            return Response::make('Unauthorized', 401);
        } else {
            return Redirect::to("/mship/manage/landing");
        }
    }
});

Route::filter('auth.user.full', function() {
    if (!Session::get("auth_true", false) OR Session::get("auth_account", 0) == 0) {
        if (Request::ajax()) {
            return Response::make('Unauthorized', 401);
        } else {
            return Redirect::to("/mship/manage/landing");
        }
    }
});

Route::filter('auth.admin', function() {
    if(!Auth::admin()->check()){
        if(Request::ajax()){
            return Response::make("Unauthorised", 401);
        } else {
            return Redirect::route("adm");
        }
    } else {
        if(!Auth::admin()->get()->hasPermission(Request::decodedPath())){
            return Redirect::route("adm.error", [401]);
        }
    }
});

Route::filter('csrf', function()
{
	if (Session::token() != Input::get('_token'))
	{
		throw new Illuminate\Session\TokenMismatchException;
	}
});