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
        if ($account->requiresMandatoryPasswordSetup()) {
            return redirect()
                ->route('login.password.setup')
                ->withError('You are required to set a secondary password before continuing.');
        }

        if ($account->hasPassword()) {
            return redirect()->route('auth-secondary');
        }

        return self::establishWebSession($request, $account, true);
    }

    public static function redirectAfterMandatoryPasswordSetup(Request $request, Account $account): Response
    {
        return self::establishWebSession($request, $account, true, passwordVerified: true);
    }

    public static function establishWebSession(
        Request $request,
        Account $account,
        bool $remember,
        bool $passwordVerified = false,
    ): Response {
        $intended = Session::pull('url.intended', route('site.home'));

        Auth::login($account, $remember);

        if ($response = TwoFactorLoginRedirect::afterSuccessfulLogin(
            $request,
            $account,
            $remember,
            $passwordVerified,
        )) {
            return $response;
        }

        return redirect($intended);
    }
}
