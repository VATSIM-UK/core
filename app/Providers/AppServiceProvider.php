<?php

namespace App\Providers;

use HTML;
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

        HTML::component('icon', 'components.html.icon', ['type', 'key']);
        HTML::component('img', 'components.html.img', ['key', 'ext' => 'png', 'width' => null, 'height' => null, 'alt' => null]);
        HTML::component('panelOpen', 'components.html.panel_open', ['title', 'icon' => [], 'attr' => []]);
        HTML::component('panelClose', 'components.html.panel_close', []);
        HTML::component('fuzzyDate', 'components.html.fuzzy_date', ['timestamp']);
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //        if ($this->app->environment() == 'development') {
//            $this->app->register('Laracasts\Generators\GeneratorsServiceProvider');
//        }

        $this->app->alias('bugsnag.logger', \Illuminate\Contracts\Logging\Log::class);
        $this->app->alias('bugsnag.logger', \Psr\Log\LoggerInterface::class);
    }
}
