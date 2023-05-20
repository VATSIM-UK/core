<?php

namespace App\Http\Middleware;

use Closure;
use Session;

class RedirectToIntended
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (empty(Session::get('middleware.failed')) && Session::has('url.intended')) {
            return redirect()->intended();
        }

        return $next($request);
    }
}
