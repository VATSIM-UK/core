<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Illuminate\Http\Middleware\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for the application.
     *
     * @var array<int, string>|string|null
     */
    protected $proxies;

    public function __construct()
    {
        $this->proxies = config('app.trusted_proxies');
    }

    /**
     * Sets the trusted proxies on the request.
     *
     * Nothing configured means nothing is trusted. This bypasses the framework
     * fallback that trusts the calling address on *.on-forge.com and
     * *.on-vapor.com hosts, which is decided from the client-controlled Host
     * header and would let a caller spoof its IP address and scheme.
     */
    protected function setTrustedProxyIpAddresses(Request $request)
    {
        if (empty($this->proxies())) {
            return;
        }

        parent::setTrustedProxyIpAddresses($request);
    }
}
