<?php

namespace App\Http\Middleware;

use Auth;
use Closure;
use Request;
use Session;
use Redirect;
use Response;

class AuthUserFull
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (!Auth::check() || !Session::has('auth_extra')) {
            if (Request::ajax()) {
                return Response::make('Unauthorised', 401);
            } else {
                Session::put('auth_return', Request::fullUrl());

                return Redirect::route('mship.auth.redirect');
            }
        }

        return $next($request);
    }
}
