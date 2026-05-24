<?php

namespace App\Http\Responses\Auth;

use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\TwoFactorConfirmedResponse as TwoFactorConfirmedResponseContract;

class TwoFactorConfirmedResponse implements TwoFactorConfirmedResponseContract
{
    public function toResponse($request)
    {
        if ($request->wantsJson()) {
            return new JsonResponse('', 200);
        }

        return redirect()
            ->route('two-factor.backup-codes')
            ->withSuccess('Two-factor authentication has been enabled for your account.');
    }
}
