<?php

namespace App\Http\Middleware;

use App\Traits\Middleware\RedirectsOnFailure;
use Auth;
use Illuminate\Support\Facades\Log;

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
                Log::warning('Access denied: 2FA not enrolled', ['account_id' => auth()->user()->id]);

                return redirect()->guest(route('two-factor.setup'))
                    ->withError('You are required to set up two-factor authentication before continuing.');
            }

            return true;
        }
    }
}
