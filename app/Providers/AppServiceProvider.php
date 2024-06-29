<?php

namespace App\Providers;

use App\Http\Controllers\BaseController;
use App\Http\Responses\LogoutResponse;
use App\Libraries\Discord;
use App\Libraries\Forum;
use App\Libraries\UKCP;
use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Filament\Http\Responses\Auth\Contracts\LogoutResponse as LogoutResponseContract;
use HTML;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Whitecube\LaravelCookieConsent\Facades\Cookies;

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

        Bugsnag::registerCallback(function ($report) {
            if (Auth::check()) {
                $user = Auth::user();

                $report->setUser([
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ]);
            }
        });

        if ($this->app->runningInConsole()) {
            URL::forceRootUrl(env('APP_PROTOCOL', 'https').'://'.Config::get('app.url'));
        }

        $this->registerHTMLComponents();
        $this->registerValidatorExtensions();

        View::composer('layout*', function ($view) {
            $view->with('_bannerUrl', BaseController::generateBannerUrl());
        });

        RateLimiter::for('discord-sync', function (object $job) {
            return Limit::perMinute(100)
                ->perHour(1000)
                ->by('discord-api-call');
        });

        Cookies::essentials()
            ->session()
            ->csrf();
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->register(TelescopeServiceProvider::class);

        $this->app->singleton(UKCP::class);
        $this->app->singleton(Discord::class);
        $this->app->singleton(Forum::class);
        $this->app->bind(LogoutResponseContract::class, LogoutResponse::class);
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
    }
}
