<?php

namespace App\Http\Middleware;

use Auth;
use Closure;
use Illuminate\Support\Facades\Log;

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
                Log::warning('Access denied: banned account', ['account_id' => optional(auth()->user())->id, 'path' => $request->path(), 'ip' => $request->ip()]);

                return response()->json(['error' => 'You are currently banned.'], 403);
            }

            if (Auth::user()->is_network_banned) {
                Log::warning('Access denied: banned account', ['account_id' => optional(auth()->user())->id, 'path' => $request->path(), 'ip' => $request->ip()]);

                return redirect()->route('banned.network');
            }

            if (Auth::user()->is_system_banned) {
                Log::warning('Access denied: banned account', ['account_id' => optional(auth()->user())->id, 'path' => $request->path(), 'ip' => $request->ip()]);

                return redirect()->route('banned.local');
            }

            Log::warning('Access denied: banned account', ['account_id' => optional(auth()->user())->id, 'path' => $request->path(), 'ip' => $request->ip()]);

            abort(403, 'You are currently banned.');
        }

        return $next($request);
    }
}
