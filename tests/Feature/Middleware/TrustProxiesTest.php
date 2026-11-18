<?php

namespace Tests\Feature\Middleware;

use App\Http\Middleware\TrustProxies;
use Illuminate\Http\Request;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class TrustProxiesTest extends TestCase
{
    #[Test]
    public function test_nothing_is_trusted_when_no_proxies_are_configured()
    {
        config()->set('app.trusted_proxies', null);

        $request = $this->requestFor('http://core.test/');

        (new TrustProxies)->handle($request, fn () => response(''));

        $this->assertSame('1.2.3.4', $request->ip());
        $this->assertFalse($request->isSecure());
    }

    #[Test]
    public function test_configured_proxies_are_trusted()
    {
        config()->set('app.trusted_proxies', '1.2.3.4, 5.6.7.8');

        $request = $this->requestFor('http://core.test/', '5.6.7.8');

        (new TrustProxies)->handle($request, fn () => response(''));

        $this->assertSame('9.9.9.9', $request->ip());
        $this->assertTrue($request->isSecure());
    }

    #[Test]
    public function test_cloudflare_ranges_are_trusted_by_default()
    {
        $this->assertContains('173.245.48.0/20', config('app.trusted_proxies'));

        $request = $this->requestFor('http://core.test/', '173.245.48.10');

        (new TrustProxies)->handle($request, fn () => response(''));

        $this->assertSame('9.9.9.9', $request->ip());
        $this->assertTrue($request->isSecure());
    }

    #[Test]
    public function test_platform_hosts_do_not_trust_an_unconfigured_caller()
    {
        config()->set('app.trusted_proxies', null);

        $request = $this->requestFor('http://core.on-forge.com/');

        (new TrustProxies)->handle($request, fn () => response(''));

        $this->assertSame('1.2.3.4', $request->ip());
        $this->assertFalse($request->isSecure());
    }

    private function requestFor(string $url, string $remoteAddress = '1.2.3.4'): Request
    {
        return Request::create($url, 'GET', [], [], [], [
            'REMOTE_ADDR' => $remoteAddress,
            'HTTP_X_FORWARDED_FOR' => '9.9.9.9',
            'HTTP_X_FORWARDED_PROTO' => 'https',
        ]);
    }
}
