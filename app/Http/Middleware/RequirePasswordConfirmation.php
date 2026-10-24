<?php

namespace App\Http\Middleware;

use App\Models\Mship\Account;
use Closure;
use Illuminate\Http\Request;

class RequirePasswordConfirmation
{
    public function handle(Request $request, Closure $next, ?string $redirectToRoute = null)
    {
        if ($this->passwordConfirmedRecently($request)) {
            return $next($request);
        }

        return redirect()->guest(
            route('two-factor.confirm-password', [
                'redirect' => $request->fullUrl(),
            ])
        );
    }

    protected function passwordConfirmedRecently(Request $request): bool
    {
        $user = $request->user();

        // Accounts without a secondary password have nothing to confirm. We skip
        if ($user instanceof Account && ! $user->hasPassword()) {
            return true;
        }

        $confirmedAt = $request->session()->get('auth.password_confirmed_at', 0);

        return $confirmedAt >= now()->subSeconds(10800)->unix();
    }
}
