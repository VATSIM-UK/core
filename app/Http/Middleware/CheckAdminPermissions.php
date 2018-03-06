<?php

namespace App\Http\Middleware;

use Closure;

class CheckAdminPermissions
{
    protected $except = [
        'adm/smartcars',
    ];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($this->shouldPassThrough($request)) {
            return $next($request);
        }

        if (!$request->user()->hasPermission($request->decodedPath())) {
            abort(403);
        }

        return $next($request);
    }

    /**
     * Determine if the request has a URI that should pass through.
     *
     * @param  \Illuminate\Http\Request $request
     * @return bool
     */
    protected function shouldPassThrough($request)
    {
        foreach ($this->except as $except) {
            if ($except !== '/') {
                $except = trim($except, '/');
            }

            if (starts_with($request->decodedPath(), $except)) {
                return true;
            }
        }

        return false;
    }
}
