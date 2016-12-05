<?php

namespace App\Http\Middleware;

use Auth;
use Closure;
use Session;
use Carbon\Carbon;

class TrackInactivity
{
    protected $except = [
        'mship/auth/logout/1',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($this->shouldPassThrough($request)) {
            return $next($request);
        }

        if (Auth::check() && Session::has('last_activity')) {
            $timeout = Auth::user()->session_timeout;

            $inactive = Carbon::now()->diffInMinutes(Session::get('last_activity'));

            if ($timeout !== 0 && $inactive >= $timeout) {
                // forget their secondary authentication
                Session::forget('auth_extra');
            }
        }

        // process the request
        $response = $next($request);

        // update their activity after the request has been processed
        Session::put('last_activity', Carbon::now());

        return $response;
    }

    /**
     * Determine if the request has a URI that should pass through.
     *
     * Method used from Illuminate\Foundation\Http\Middleware\VerifyCsrfToken
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function shouldPassThrough($request)
    {
        foreach ($this->except as $except) {
            if ($except !== '/') {
                $except = trim($except, '/');
            }

            if ($request->is($except)) {
                return true;
            }
        }

        return false;
    }
}
