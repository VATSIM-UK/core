<?php

namespace App\Providers;

use App\Models\Mship;
use App\Models\Sys;
use App\Models\VisitTransfer;
use App\Observers\ModelActivityObserver;
use Illuminate\Support\ServiceProvider;

class ObserverServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        Mship\Account::observe(ModelActivityObserver::class);
        Mship\Account\Ban::observe(ModelActivityObserver::class);
        Mship\Account\Note::observe(ModelActivityObserver::class);
        Mship\Permission::observe(ModelActivityObserver::class);
        Mship\Role::observe(ModelActivityObserver::class);
        Sys\Notification::observe(ModelActivityObserver::class);
        VisitTransfer\Application::observe(ModelActivityObserver::class);
        VisitTransfer\Reference::observe(ModelActivityObserver::class);
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
