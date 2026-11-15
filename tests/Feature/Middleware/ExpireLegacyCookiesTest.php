<?php

namespace Tests\Feature\Middleware;

use Tests\TestCase;

class ExpireLegacyCookiesTest extends TestCase
{
    public function test_parent_domain_cookies_are_expired_without_expiring_the_host_only_ones(): void
    {
        $response = $this->get(route('site.home'));

        $legacy = collect($response->headers->getCookies())
            ->filter(fn ($cookie) => $cookie->getDomain() === '.vatsim.uk');

        $this->assertEqualsCanonicalizing(
            ['vukcore_core_session', 'XSRF-TOKEN'],
            $legacy->map(fn ($cookie) => $cookie->getName())->values()->all(),
        );
        $this->assertTrue($legacy->every(fn ($cookie) => $cookie->getExpiresTime() < time()));

        $hostOnly = collect($response->headers->getCookies())
            ->first(fn ($cookie) => $cookie->getName() === config('session.cookie') && $cookie->getDomain() === null);

        $this->assertNotNull($hostOnly);
        $this->assertGreaterThan(time(), $hostOnly->getExpiresTime());
    }
}
