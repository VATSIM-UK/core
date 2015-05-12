<?php namespace App\Providers;

use Config;
use Request;
use Illuminate\Support\ServiceProvider;

class ProxyServiceProvider extends ServiceProvider
{
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $request = $this->app['request'];
        $proxies = $this->app['config']->get('app.proxies');

        if ($proxies === '*') {
            $proxies = array($request->getClientIp());
        }

        $request->setTrustedProxies($proxies);
    }
}
