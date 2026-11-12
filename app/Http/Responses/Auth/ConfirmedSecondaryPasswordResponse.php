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

        // Only follow a path on this site: parse_url's host is spoofable, e.g.
        // "https://evil.example\@core.test/" parses as core.test but navigates to evil.example.
        $parts = parse_url($redirect);
        $appHost = parse_url(config('app.url'), PHP_URL_HOST);

        $isRelative = Str::startsWith($redirect, '/') && ! Str::startsWith($redirect, '//');
        $isSameHost = is_array($parts) && is_string($appHost) && is_string($parts['host'] ?? null) && $parts['host'] === $appHost;

        if (! is_array($parts) || str_contains($redirect, '\\') || (! $isRelative && ! $isSameHost)) {
            return redirect()->to($defaultRedirect);
        }

        $target = '/'.ltrim($parts['path'] ?? '', '/');

        if (isset($parts['query'])) {
            $target .= '?'.$parts['query'];
        }

        return redirect()->to($target);
    }
}
