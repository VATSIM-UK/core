<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use \Form;
use \HTML;

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

        HTML::component("icon", "components.html.icon", ["type", "key"]);
        HTML::component("panelOpen", "components.html.panel_open", ["title", "icon" => []]);
        HTML::component("panelClose", "components.html.panel_close", []);
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
