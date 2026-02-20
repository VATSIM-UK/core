<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class TrainingPanelAccessMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        $account = $request->user();

        if (! $account) {
            Session::put('url.intended', $request->url());

            return redirect()->route('login');
        }

        if (! $account->can('training.access')) {
            abort(404);
        }

        return $next($request);
    }
}
