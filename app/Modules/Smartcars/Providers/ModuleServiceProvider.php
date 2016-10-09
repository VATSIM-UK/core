<?php

namespace App\Modules\Smartcars\Providers;

use Caffeinated\Modules\Support\ServiceProvider;
use Response;

class ModuleServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the module services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadTranslationsFrom(__DIR__.'/../Resources/Lang', 'smartcars');
        $this->loadViewsFrom(__DIR__.'/../Resources/Views', 'smartcars');

        Response::macro("csv", function($value){
            return Response::make(implode(",",$value));
        });

        Response::macro("psv", function($value){
            return Response::make(implode("|",$value));
        });
    }

    /**
     * Register the module services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
    }
}
