<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class NovaServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (!class_exists('\Laravel\Nova\Nova')) {
            return;
        }

        $this->routes();

        \Laravel\Nova\Nova::serving(function (\Laravel\Nova\Events\ServingNova $event) {
            $this->authorization();

            $this->resources();
            \Laravel\Nova\Nova::cards($this->cards());
            \Laravel\Nova\Nova::tools($this->tools());
        });
    }

    /**
     * Register the Nova routes.
     *
     * @return void
     */
    protected function routes()
    {
        if (!class_exists('\Laravel\Nova\Nova')) {
            return;
        }

        \Laravel\Nova\Nova::routes()
            ->withAuthenticationRoutes()
            ->withPasswordResetRoutes()
            ->register();
    }

    /**
     * Configure the Nova authorization services.
     *
     * @return void
     */
    protected function authorization()
    {
        if (!class_exists('\Laravel\Nova\Nova')) {
            return;
        }

        $this->gate();

        \Laravel\Nova\Nova::auth(function ($request) {
            return !app()->environment('production') ||
                Gate::check('viewNova', [$request->user()]);
        });
    }

    /**
     * Register the Nova gate.
     *
     * This gate determines who can access Nova in non-local environments.
     *
     * @return void
     */
    protected function gate()
    {
        Gate::define('viewNova', function ($user) {
            return in_array($user->email, [
                //
            ]);
        });
    }

    /**
     * Get the cards that should be displayed on the Nova dashboard.
     *
     * @return array
     */
    protected function cards()
    {
        return [
            new \Laravel\Nova\Cards\Help,
        ];
    }

    /**
     * Get the tools that should be listed in the Nova sidebar.
     *
     * @return array
     */
    public function tools()
    {
        return [
            \Vyuldashev\NovaPermission\NovaPermissionTool::make(),
        ];
    }

    /**
     * Register the application's Nova resources.
     *
     * @return void
     */
    protected function resources()
    {
        \Laravel\Nova\Nova::resourcesIn(app_path('Nova'));
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
