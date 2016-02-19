<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use \Form;

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

        Form::component("bsButton", "components.form.button", ["value", "options" => []]);
        Form::component("bsSubmit", "components.form.submit", ["value", "options" => []]);
        Form::component("bsText", "components.form.text", ["name", "value" => null, "attributes" => [], "hint" => []]);
        Form::component("bsPassword", "components.form.password", ["name", "attributes" => [], "hint" => []]);
        Form::component("bsTextArea", "components.form.textarea", ["name", "value" => null, "attributes" => [], "hint" => []]);
        Form::component("hint", "components.form.hint", ["text", "link" => null]);
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
