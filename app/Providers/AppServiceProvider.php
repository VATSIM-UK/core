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
        if (!defined("VATUK_ACCOUNT_SYSTEM")) {
            define("VATUK_ACCOUNT_SYSTEM", "707070");
        }

        if (!defined("VATSIM_ACCOUNT_SYSTEM")) {
            define("VATSIM_ACCOUNT_SYSTEM", "606060");
        }

        // Ensure that the VATSIM UK System accounts are in existence.
        // If this is not protected, we cannot run any artisan commands if there's an issue with the database.
        if (!$this->app->runningInConsole()) {
            $check = \App\Models\Mship\Account::find(VATUK_ACCOUNT_SYSTEM);
            if (!is_object($check) || !$check->exists) {
                $a = new \App\Models\Mship\Account();
                $a->account_id = VATUK_ACCOUNT_SYSTEM;
                $a->name_first = "VATSIM";
                $a->name_last = "UK";
                $a->is_system = true;
                $a->save();

                // Add all required emails by this account.
                $a->addEmail("no-reply@vatsim-uk.co.uk", true, true);
            }
        }

        // Ensure that the VATSIM.NET System accounts are in existence.
        // If this is not protected, we cannot run any artisan commands if there's an issue with the database.
        if (!$this->app->runningInConsole()) {
            $check = \App\Models\Mship\Account::find(VATSIM_ACCOUNT_SYSTEM);
            if (!is_object($check) || !$check->exists) {
                $a = new \App\Models\Mship\Account();
                $a->account_id = VATSIM_ACCOUNT_SYSTEM;
                $a->name_first = "VATSIM";
                $a->name_last = "NET";
                $a->is_system = true;
                $a->save();

                // Add all required emails by this account.
                $a->addEmail("no-reply@vatsim.net", true, true);
            }
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
