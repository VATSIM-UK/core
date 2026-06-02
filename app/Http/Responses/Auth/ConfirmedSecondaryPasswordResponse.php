<?php

namespace App\Http\Responses\Auth;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Laravel\Fortify\Contracts\PasswordConfirmedResponse as PasswordConfirmedResponseContract;

class ConfirmedSecondaryPasswordResponse implements PasswordConfirmedResponseContract
{
    public function toResponse($request)
    {
        if ($request->wantsJson()) {
            return new JsonResponse('', 201);
        }

        $redirect = $request->input('redirect') ?? $request->query('redirect');
        $defaultRedirect = route('mship.manage.dashboard');

        if (! is_string($redirect) || blank($redirect)) {
            return redirect()->to($defaultRedirect);
        }

        if (Str::startsWith($redirect, '/') && ! Str::startsWith($redirect, '//')) {
            return redirect()->to($redirect);
        }

        $appHost = parse_url(config('app.url'), PHP_URL_HOST);
        $redirectHost = parse_url($redirect, PHP_URL_HOST);

        if (is_string($appHost) && is_string($redirectHost) && $appHost === $redirectHost) {
            return redirect()->to($redirect);
        }

        return redirect()->to($defaultRedirect);
    }
}
