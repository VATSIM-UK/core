<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class AdminPanelFilamentAccessMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response|RedirectResponse)  $next
     * @return Response|RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {

        $account = $request->user();

        if (! $account) {
            Session::put('url.intended', $request->url());

            return redirect()->route('login');
        }

        if (! $account->can('admin.access')) {
            Log::warning('Access denied: admin panel access', ['account_id' => $account->id, 'path' => $request->path(), 'ip' => $request->ip()]);

            return abort(404);
        }

        return $next($request);
    }
}
