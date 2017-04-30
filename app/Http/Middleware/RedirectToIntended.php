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
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (Session::has('url.intended')) {
            return redirect()->intended();
        }

        return $next($request);
    }
}
