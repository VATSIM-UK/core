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
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Auth::check() && Auth::user()->is_banned) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'You are currently banned.'], 403);
            }

            abort(403, 'You are currently banned.');
        }

        return $next($request);
    }
}
