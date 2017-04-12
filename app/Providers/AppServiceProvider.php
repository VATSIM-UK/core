<?php

namespace App\Providers;

use URL;
use HTML;
use Config;
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
        if ($this->app->runningInConsole()) {
            URL::forceRootUrl(env('APP_PROTOCOL', 'https').'://'.Config::get('app.url'));
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
        $this->app->alias('bugsnag.multi', \Illuminate\Contracts\Logging\Log::class);
        $this->app->alias('bugsnag.multi', \Psr\Log\LoggerInterface::class);
    }
}
