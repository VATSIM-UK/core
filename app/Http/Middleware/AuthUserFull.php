<?php

namespace App\Http\Middleware;

use Auth;
use Closure;
use Response;
use Request;
use Redirect;
use Session;

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
                return Redirect::route("mship.auth.redirect");
            }
        }
        
        return $next($request);
    }
}
