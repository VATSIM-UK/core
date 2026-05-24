<?php

namespace App\Http\Responses\Auth;

use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\PasswordConfirmedResponse as PasswordConfirmedResponseContract;

class ConfirmedSecondaryPasswordResponse implements PasswordConfirmedResponseContract
{
    public function toResponse($request)
    {
        if ($request->wantsJson()) {
            return new JsonResponse('', 201);
        }

        $redirect = $request->input('redirect') ?? $request->query('redirect');

        return redirect()->to($redirect ?? route('mship.manage.dashboard'));
    }
}
