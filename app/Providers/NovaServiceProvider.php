<?php

namespace App\Providers;

use App\Models\Mship\Account;
use App\Nova\Feedback;
use App\Nova\FeedbackResponse;
use App\Nova\WaitingList;
use App\Nova\WaitingListFlag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Nova\Nova;

class NovaServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        if (! class_exists('\Laravel\Nova\Nova')) {
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
        if (! class_exists('\Laravel\Nova\Nova')) {
            return;
        }

        \Laravel\Nova\Nova::routes()
            ->register();
    }

    /**
     * Configure the Nova authorization services.
     *
     * @return void
     */
    protected function authorization()
    {
        if (! class_exists('\Laravel\Nova\Nova')) {
            return;
        }

        $this->gate();

        \Laravel\Nova\Nova::auth(function (Request $request) {
            return Gate::check('accessNova', [$request->user()]);
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
        Gate::define('accessNova', function (Account $account) {
            return $account->can('use-permission', 'nova');
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
            // \Vyuldashev\NovaPermission\NovaPermissionTool::make()
        ];
    }

    /**
     * Register the application's Nova resources.
     *
     * @return void
     */
    protected function resources()
    {
        if (! app()->environment('local')) {
            Nova::resources([
                WaitingList::class,
                WaitingListFlag::class,
                Feedback::class,
                FeedbackResponse::class,
            ]);
        } else {
            \Laravel\Nova\Nova::resourcesIn(app_path('Nova'));
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
