<?php

namespace App\Modules\Ais\Providers;

use Lang;
use View;
use Caffeinated\Modules\Support\AuthServiceProvider;

class ModuleServiceProvider extends AuthServiceProvider
{
    protected $policies = [
    ];

    /**
     * Bootstrap the module services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadTranslationsFrom(__DIR__.'/../Resources/Lang', 'ais');
        $this->loadViewsFrom(__DIR__.'/../Resources/Views', 'ais');
        $this->registerPolicies();
    }

    /**
     * Register the module services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(RouteServiceProvider::class);
        $this->app->register(EventServiceProvider::class);

        $this->registerNamespaces();
        $this->registerComposers();
        $this->registerComposers();
    }

    /**
     * Register the AIS resource namespaces.
     *
     * @return void
     */
    protected function registerNamespaces()
    {
        Lang::addNamespace('ais', realpath(__DIR__.'/../Resources/Lang'));

        View::addNamespace('ais', base_path('resources/views/vendor/networkdata'));
        View::addNamespace('ais', realpath(__DIR__.'/../Resources/Views'));
    }

    /**
     * Register the NetworkData module composers.
     *
     * @return void
     */
    protected function registerComposers()
    {
        //        View::composer(
//            ['ais::admin._sidebar'],
//            \App\Modules\Ais\Resources\Viewcomposers\StatisticsComposer::class
//        );
    }
}
