<?php

namespace App\Http\Middleware;

use Auth;
use Closure;

class RecordLoginInfo
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $account = Auth::user();

        // Do we need to do some debugging on this user?
        if ($account->debug) {
            \Debugbar::enable();
        }

        // if last login recorded is older than 45 minutes, record the new timestamp
        if ($account->last_login < \Carbon\Carbon::now()->subMinutes(45)->toDateTimeString()) {
            $account->last_login = \Carbon\Carbon::now();

            // if the ip has changed, record this too
            $ip = $request->ip();
            if ($account->last_login_ip != $ip) {
                $account->last_login_ip = $ip;
            }

            $account->save();
        }

        return $next($request);
    }
}
