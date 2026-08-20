<?php

namespace App\Http\Responses\Auth;

use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\TwoFactorLoginResponse as TwoFactorLoginResponseContract;

class TwoFactorLoginResponse implements TwoFactorLoginResponseContract
{
    public function toResponse($request)
    {
        if ($request->wantsJson()) {
            return new JsonResponse('', 204);
        }

        if ($request->filled('recovery_code')) {
            return redirect()->route('two-factor.setup')
                ->withSuccess('You signed in using a recovery code. Here are your remaining recovery codes.');
        }

        return redirect()->intended(route('mship.manage.dashboard'));
    }
}
