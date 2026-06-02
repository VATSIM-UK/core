<?php

declare(strict_types=1);

namespace App\Auth;

use App\Models\Mship\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class TwoFactorLoginRedirect
{
    public static function afterSuccessfulLogin(
        Request $request,
        Account $user,
        bool $remember = false,
        bool $passwordVerified = false,
    ): ?Response {
        if ($user->requiresTwoFactorChallenge()) {
            $request->session()->put([
                'login.id' => $user->getKey(),
                'login.remember' => $remember,
            ]);

            if ($passwordVerified) {
                $request->session()->put('auth.password_confirmed_at', now()->unix());
            }

            Auth::guard('web')->logout();

            return redirect()->route('two-factor.login');
        }

        $request->session()->put('auth.password_confirmed_at', now()->unix());

        return null;
    }
}
