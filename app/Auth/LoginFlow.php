<?php

declare(strict_types=1);

namespace App\Auth;

use App\Models\Mship\Account;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class LoginFlow
{
    public static function redirectAfterVatsimOAuth(Request $request, Account $account): Response
    {
        return self::establishWebSession($request, $account, false);
    }

    public static function establishWebSession(
        Request $request,
        Account $account,
        bool $remember,
    ): Response {
        $intended = Session::pull('url.intended', route('site.home'));

        // Callers never force remember-me, so a recalled session cannot outlive the inactivity timeout.
        Auth::login($account, $remember);

        if ($response = TwoFactorLoginRedirect::afterSuccessfulLogin($request, $account, $remember)) {
            Session::put('url.intended', $intended);

            return $response;
        }

        return redirect($intended);
    }
}
