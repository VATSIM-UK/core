<?php

namespace App\Providers;

use App\Http\Controllers\BaseController;
use App\Libraries\Discord;
use App\Libraries\UKCP;
use HTML;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Laravel\Telescope\Telescope;

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

        $this->registerHTMLComponents();
        $this->registerValidatorExtensions();

        View::composer('layout*', function ($view) {
            $view->with('_bannerUrl', BaseController::generateBannerUrl());
        });
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(TelescopeServiceProvider::class);
        Telescope::ignoreMigrations();

        $this->app->singleton(UKCP::class);
        $this->app->singleton(Discord::class);
    }

    public function registerHTMLComponents()
    {
        HTML::component('icon', 'components.html.icon', ['type', 'key']);
        HTML::component('img', 'components.html.img', ['key', 'ext' => 'png', 'width' => null, 'height' => null, 'alt' => null]);
        HTML::component('panelOpen', 'components.html.panel_open', ['title', 'icon' => [], 'attr' => []]);
        HTML::component('panelClose', 'components.html.panel_close', []);
        HTML::component('fuzzyDate', 'components.html.fuzzy_date', ['timestamp']);
    }

    public function registerValidatorExtensions()
    {
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

        /*
            Helper macro to configure an API request to the VATSIM API

            $path: path after the last slash in the configured API URL
            $includeBase: whether or not the base path should be used when constructing the URL
            $method: type of HTTP request to make. Defaults to GET.

            TODO: Open up possibility of sending post request with body.
            TODO: When using PHP8, named_arguments would make this much nicer to use from a developer perspective.
        */
        Http::macro('vatsimAPIRequest', function ($path, $includeBase = true, $method = 'get') {
            $apiToken = config('vatsim-api.key');
            $url = $includeBase ? config('vatsim-api.base').$path : $path;

            return Http::withHeaders([
                'Authorization' => "Token {$apiToken}",
            ])->$method($url)->json();
        });
    }
}
