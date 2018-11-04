<?php

namespace App\Http\Middleware;

use Closure;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

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

        $globalPermission = $request->user()->hasRole('privacc');

        $routePermission = preg_replace('/[0-9]+/', '*', $request->decodedPath()); // Remove anything that looks like a number (its likely its an ID)

        try {
            $hasRoutePermission = $request->user('web')->hasPermissionTo($routePermission); // Check for permission to use route
        } catch (PermissionDoesNotExist $e) {

            // Permission doesn't exist, lets look for a higher level.

            $fullUri = explode('/', $routePermission); // Split to array

            array_pop($fullUri); // Remove last item (specific URL)

            $newUri = implode('/', $fullUri).'/*'; // Replace last item with /*

            $newUri = str_replace('/*/*', '/*', $newUri); // If the new url results in /*/*, we only want the highest level

            $request->user('web')->hasPermissionTo($newUri);
        }

        if (!$globalPermission && !$hasRoutePermission) {
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
