<?php

namespace App\Http\Middleware;

use App\Traits\Middleware\RedirectsOnFailure;
use Auth;

class MandatoryTwoFactor
{
    use RedirectsOnFailure;

    protected $except = [
        'auth/two-factor/*',
        'password/create',
        'password/change',
        'logout',
    ];

    public function validate($makeResponse)
    {
        if (Auth::check() && Auth::user()->requiresTwoFactorSetup()) {
            if ($makeResponse) {
                return redirect()->guest(route('two-factor.setup'))
                    ->withError('You are required to set up two-factor authentication before continuing.');
            }

            return true;
        }
    }
}
