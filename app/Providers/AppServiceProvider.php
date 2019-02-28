<?php

namespace App\Providers;

use App\Http\Controllers\BaseController;
use Bugsnag;
use Config;
use HTML;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use URL;
use Validator;
use View;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);

        if ($this->app->runningInConsole()) {
            URL::forceRootUrl(env('APP_PROTOCOL', 'https').'://'.Config::get('app.url'));
        }

        $this->registerBugsnagCallback();

        View::composer('layout*', function ($view) {
            $view->with('_bannerUrl', BaseController::generateBannerUrl());
        });

        HTML::component('icon', 'components.html.icon', ['type', 'key']);
        HTML::component('img', 'components.html.img', ['key', 'ext' => 'png', 'width' => null, 'height' => null, 'alt' => null]);
        HTML::component('panelOpen', 'components.html.panel_open', ['title', 'icon' => [], 'attr' => []]);
        HTML::component('panelClose', 'components.html.panel_close', []);
        HTML::component('fuzzyDate', 'components.html.fuzzy_date', ['timestamp']);

        // if necessary, these can extend the Laravel validator, see:
        // https://www.sitepoint.com/data-validation-laravel-right-way-custom-validators/
        Validator::extend('upperchars', function ($attribute, $value, $parameters, $validator) {
            if (isset($parameters[0])) {
                return str_has_upper($value, $parameters[0]);
            } else {
                return str_has_upper($value);
            }
        });

        Validator::replacer('upperchars', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':min', $parameters[0], $message);
        });

        Validator::extend('lowerchars', function ($attribute, $value, $parameters, $validator) {
            if (isset($parameters[0])) {
                return str_has_lower($value, $parameters[0]);
            } else {
                return str_has_lower($value);
            }
        });

        Validator::replacer('lowerchars', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':min', $parameters[0], $message);
        });

        Validator::extend('numbers', function ($attribute, $value, $parameters, $validator) {
            if (isset($parameters[0])) {
                return str_has_lower($value, $parameters[0]);
            } else {
                return str_has_lower($value);
            }
        });

        Validator::replacer('numbers', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':min', $parameters[0], $message);
        });

        Validator::extend('password', function ($attribute, $value, $parameters, $validator) {
            return \Auth::user()->verifyPassword($value);
        });
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

        if ($this->app->isLocal()) {
            $this->app->register(TelescopeServiceProvider::class);
        }
    }

    /**
     * Register a Bugsnag callback to avoid grouping
     * SQL errors by the Model::save function.
     */
    private function registerBugsnagCallback()
    {
        Bugsnag::registerCallback(function ($report) {
            $stacktrace = $report->getStacktrace();
            $frames = $stacktrace->getFrames();

            foreach ($frames as &$frame) {
                if ($frame['file'] === 'app/Models/Model.php' && $frame['method'] === 'App\\Models\\Model::save') {
                    $frame['inProject'] = false;
                }
            }
        });
    }
}
