<?php

namespace App\Http\Middleware;

use Auth;
use Closure;

class DenyIfBanned
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::check() && Auth::user()->is_banned) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'You are currently banned.'], 403);
            }

            if (Auth::user()->is_network_banned) {
                return redirect()->route('banned.network');
            }

            if (Auth::user()->is_system_banned) {
                return redirect()->route('banned.local');
            }

            abort(403, 'You are currently banned.');
        }

        return $next($request);
    }
}
