<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (!defined('VATUK_ACCOUNT_SYSTEM')) {
            define('VATUK_ACCOUNT_SYSTEM', '707070');
        }

        if (!defined('VATSIM_ACCOUNT_SYSTEM')) {
            define('VATSIM_ACCOUNT_SYSTEM', '606060');
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
