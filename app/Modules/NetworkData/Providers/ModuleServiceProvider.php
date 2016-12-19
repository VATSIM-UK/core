<?php

namespace App\Modules\NetworkData\Providers;

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
        $this->loadTranslationsFrom(__DIR__.'/../Resources/Lang', 'networkdata');
        $this->loadViewsFrom(__DIR__.'/../Resources/Views', 'networkdata');
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
     * Register the VATSIM Network Data module resource namespaces.
     *
     * @return void
     */
    protected function registerNamespaces()
    {
        Lang::addNamespace('networkdata', realpath(__DIR__.'/../Resources/Lang'));

        View::addNamespace('networkdata', base_path('resources/views/vendor/networkdata'));
        View::addNamespace('networkdata', realpath(__DIR__.'/../Resources/Views'));
    }

    /**
     * Register the NetworkData module composers.
     *
     * @return void
     */
    protected function registerComposers()
    {
        //        View::composer(
//            ['networkdata::admin._sidebar'],
//            \App\Modules\Networkdata\Resources\Viewcomposers\StatisticsComposer::class
//        );
    }
}
