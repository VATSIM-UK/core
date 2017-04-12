<?php

namespace App\Modules\Visittransfer\Providers;

use Lang;
use View;
use App\Modules\Visittransfer\Models\Reference;
use App\Modules\Visittransfer\Models\Application;
use Caffeinated\Modules\Support\AuthServiceProvider;
use App\Modules\Visittransfer\Policies\ReferencePolicy;
use App\Modules\Visittransfer\Policies\ApplicationPolicy;

class ModuleServiceProvider extends AuthServiceProvider
{
    protected $policies = [
        Application::class => ApplicationPolicy::class,
        Reference::class => ReferencePolicy::class,
    ];

    /**
     * Bootstrap the module services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadTranslationsFrom(__DIR__.'/../Resources/Lang', 'visittransfer');
        $this->loadViewsFrom(__DIR__.'/../Resources/Views', 'visittransfer');
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
     * Register the Visittransfer module resource namespaces.
     *
     * @return void
     */
    protected function registerNamespaces()
    {
        Lang::addNamespace('visittransfer', realpath(__DIR__.'/../Resources/Lang'));

        View::addNamespace('visittransfer', base_path('resources/views/vendor/visittransfer'));
        View::addNamespace('visittransfer', realpath(__DIR__.'/../Resources/Views'));
    }

    /**
     * Register the Visittransfer module composers.
     *
     * @return void
     */
    protected function registerComposers()
    {
        View::composer(
            ['visittransfer::admin._sidebar'],
            \App\Modules\Visittransfer\Resources\Viewcomposers\StatisticsComposer::class
        );
    }
}
