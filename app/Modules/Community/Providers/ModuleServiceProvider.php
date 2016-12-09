<?php

namespace App\Modules\Community\Providers;

use App\Modules\Community\Models\Membership;
use App\Modules\Community\Policies\MembershipPolicy;
use Caffeinated\Modules\Support\AuthServiceProvider;

class ModuleServiceProvider extends AuthServiceProvider
{
    protected $policies = [
        Membership::class => MembershipPolicy::class,
    ];

    /**
     * Bootstrap the module services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadTranslationsFrom(__DIR__.'/../Resources/Lang', 'community');
        $this->loadViewsFrom(__DIR__.'/../Resources/Views', 'community');
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
    }
}
