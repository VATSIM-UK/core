<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Log;

class CheckAdminPermissions
{
    protected $except = [];

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if ($this->shouldPassThrough($request)) {
            return $next($request);
        }

        $routePermission = preg_replace('/[0-9]+/', '*', $request->decodedPath()); // Remove anything that looks like a number (its likely its an ID)
        $hasRoutePermission = $request->user()->can('use-permission', $routePermission); // Check for permission to use route

        if ($hasRoutePermission) {
            return $next($request);
        }

        $fullUri = explode('/', $routePermission); // Split to array
        array_pop($fullUri); // Remove last item (specific URL)
        $newUri = implode('/', $fullUri).'/*'; // Replace last item with /*
        $newUri = str_replace('/*/*', '/*', $newUri); // If the new url results in /*/*, we only want the highest level

        $hasRoutePermission = $request->user()->can('use-permission', $newUri);
        if ($hasRoutePermission) {
            return $next($request);
        }

        Log::warning('Access denied: missing admin permission', ['account_id' => optional(auth()->user())->id, 'path' => $request->path(), 'ip' => $request->ip()]);

        abort(403);
    }

    /**
     * Determine if the request has a URI that should pass through.
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

            if (starts_with($request->decodedPath(), $except)) {
                return true;
            }
        }

        return false;
    }
}
