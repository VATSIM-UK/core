<?php

namespace App\Http\Middleware;

use Auth;
use Closure;
use Response;
use Request;
use Redirect;

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
        if (!Auth::check() || !Auth::user()->auth_extra) {
            if (Request::ajax()) {
                return Response::make('Unauthorised', 401);
            } else {
                return Redirect::to('/mship/auth/redirect');
            }
        }
        
        return $next($request);
    }
}
