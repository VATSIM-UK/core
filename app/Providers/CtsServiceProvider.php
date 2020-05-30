<?php

namespace App\Providers;

use App\Repositories\Cts\SessionRepository;
use Illuminate\Support\ServiceProvider;

class CtsServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->singleton(SessionRepository::class);
    }
}
