<?php

namespace App\Http\Middleware;

use Auth;
use Closure;

class MandatoryPasswords
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
        if (Auth::check() && Auth::user()->mandatory_password && !Auth::user()->hasPassword()) {
            return redirect()->route('mship.security.replace');
        }

        return $next($request);
    }
}
