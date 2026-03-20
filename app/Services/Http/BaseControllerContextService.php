<?php

namespace App\Services\Http;

use App\Models\Mship\Account;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

class BaseControllerContextService
{
    public function resolveAuthenticatedAccount(): Account
    {
        if (Auth::check() || Auth::guard('web')->check()) {
            $account = Auth::user();
            $account->load('roles', 'roles.permissions');

            return $account;
        }

        return new Account;
    }

    public function resolveRedirectTo(string $defaultRedirect): string
    {
        if (Session::has('url.intended')) {
            return (string) Session::pull('url.intended');
        }

        return $defaultRedirect;
    }

    public function hasAuthorizationErrorMessage(): bool
    {
        return Session::has('authorization.error');
    }

    public function getAuthorizationErrorMessage(): string
    {
        return (string) Session::get('authorization.error');
    }
}
