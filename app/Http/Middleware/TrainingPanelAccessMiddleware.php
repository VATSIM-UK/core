<?php

namespace App\Http\Middleware;

use Closure;

class TrainingPanelAccessMiddleware
{
    public function handle($request, Closure $next)
    {
        $account = $request->user();

        if (! $account) {
            return redirect()->route('login');
        }

        if (! $account->can('training.access')) {
            abort(404);
        }

        return $next($request);
    }
}
