<?php

namespace App\Providers;

use App\Models\Mship;
use App\Models\TeamSpeak;
use App\Models\Sso;
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
        Mship\Ban\Reason::observe(ModelActivityObserver::class);
        Mship\Note\Type::observe(ModelActivityObserver::class);
        Mship\Permission::observe(ModelActivityObserver::class);
        Mship\Qualification::observe(ModelActivityObserver::class);
        Mship\Role::observe(ModelActivityObserver::class);
        Mship\State::observe(ModelActivityObserver::class);
        TeamSpeak\Confirmation::observe(ModelActivityObserver::class);
        TeamSpeak\Registration::observe(ModelActivityObserver::class);
        Sso\Email::observe(ModelActivityObserver::class);
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
