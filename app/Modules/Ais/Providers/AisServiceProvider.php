<?php

namespace App\Modules\Ais\Providers;

use App;
use Lang;
use View;
use Illuminate\Support\ServiceProvider;

class AisServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        // You may register any additional middleware provided with your
        // module with the following addMiddleware() method. You may
        // pass in either an array or a string.
        // $this->addMiddleware('');
    }

    /**
     * Register the Airfield Information Services module service provider.
     *
     * @return void
     */
    public function register()
    {
        // This service provider is a convenient place to register your modules
        // services in the IoC container. If you wish, you may make additional
        // methods or service providers to keep the code more focused and granular.
        App::register(\App\Modules\Ais\Providers\RouteServiceProvider::class);

        $this->registerNamespaces();
    }

    /**
     * Register the Airfield Information Services module resource namespaces.
     *
     * @return void
     */
    protected function registerNamespaces()
    {
        //		Lang::addNamespace('ais', realpath(__DIR__.'/../Resources/Lang'));

        View::addNamespace('ais', base_path('resources/views/vendor/ais'));
        View::addNamespace('ais', realpath(__DIR__.'/../Resources/Views'));
    }

    /**
     * Register any additional module middleware.
     *
     * @param  array|string  $middleware
     * @return void
     */
    protected function addMiddleware($middleware)
    {
        $kernel = $this->app[\Illuminate\Contracts\Http\Kernel::class];

        if (is_array($middleware)) {
            foreach ($middleware as $ware) {
                $kernel->pushMiddleware($ware);
            }
        } else {
            $kernel->pushMiddleware($middleware);
        }
    }
}
