<?php

namespace App\Http\Middleware;

use Closure;

class CheckAdminPermissions
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
        if (!$request->user()->hasPermission($request->decodedPath())) {
            abort(403);
        }

        return $next($request);
    }
}
