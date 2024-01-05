<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class FilamentAccessMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $account = $request->user();

        if (! $account) {
            Session::put('url.intended', $request->url());
            return redirect()->route('login');
        }

        if (! $account->can('admin.access')) {
            return abort(404);
        }

        return $next($request);
    }
}
