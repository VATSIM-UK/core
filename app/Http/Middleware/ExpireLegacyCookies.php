<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class ExpireLegacyCookies
{
    private const LEGACY_DOMAIN = '.vatsim.uk';

    private const LEGACY_COOKIES = ['vukcore_core_session', 'XSRF-TOKEN'];

    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Sessions are host-only, but browsers keep sending the older parent-domain
        // copies alongside them. PHP reads whichever cookie comes first, so expire them.
        foreach (self::LEGACY_COOKIES as $name) {
            $response->headers->setCookie(Cookie::forget($name, '/', self::LEGACY_DOMAIN));
        }

        return $response;
    }
}
