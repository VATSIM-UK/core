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

Route::filter('auth.user', function() {
    if(!Auth::user()->check()){
        if(Request::ajax()){
            return Response::make("Unauthorised", 401);
        } else {
            return Redirect::to("/");
        }
    }
});

Route::filter('auth.user.full', function() {
    if(!Auth::user()->check() OR !Auth::user()->get()->auth_extra){
        if(Request::ajax()){
            return Response::make("Unauthorised", 401);
        } else {
            return Redirect::to("/mship/auth/redirect");
        }
    }
});

Route::filter('auth.admin', function() {
    if(!Auth::admin()->check()){
        if(Request::ajax()){
            return Response::make("Unauthorised", 401);
        } else {
            return Redirect::route("adm.authentication.login");
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
