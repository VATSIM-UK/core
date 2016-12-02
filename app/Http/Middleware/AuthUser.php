<?php

namespace App\Http\Middleware;

use Auth;
use Closure;
use Redirect;
use Request;
use Response;
use Session;

class AuthUser
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
        if (! Auth::check()) {
            if (Request::ajax()) {
                return Response::make('Unauthorised', 401);
            } else {
                Session::set('auth_return', Request::fullUrl());

                return Redirect::to('/');
            }
        }

        return $next($request);
    }
}
