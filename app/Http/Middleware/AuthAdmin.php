<?php

namespace App\Http\Middleware;

use Auth;
use Closure;
use Request;
use Response;
use Redirect;

class AuthAdmin
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
                return Redirect::route('adm.authentication.login');
            }
        } elseif (! Auth::user()->hasPermission(Request::decodedPath())) {
            return Redirect::route('adm.error', [401]);
        }

        return $next($request);
    }
}
