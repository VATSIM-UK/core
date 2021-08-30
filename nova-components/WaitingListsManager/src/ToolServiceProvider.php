<?php

namespace Vatsimuk\WaitingListsManager;

use Laravel\Nova\Nova;
use App\Models\Training\WaitingList;
use Laravel\Nova\Events\ServingNova;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Illuminate\Routing\Middleware\SubstituteBindings;
use App\Models\Training\TrainingPlace\TrainingPosition;
use App\Models\Training\WaitingList\WaitingListAccount;
use App\Models\Training\WaitingList\WaitingListAccountFlag;

class ToolServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->app->booted(function () {
            $this->routes();
        });

        Nova::serving(function (ServingNova $event) {
            Nova::script('waiting-lists-manager', __DIR__.'/../dist/js/tool.js');
            Nova::style('waiting-lists-manager', __DIR__.'/../dist/css/tool.css');
        });
    }

    /**
     * Register the tool's routes.
     *
     * @return void
     */
    protected function routes()
    {
        Route::model('waitingList', WaitingList::class);
        Route::model('waitingListAccount', WaitingListAccount::class);
        Route::model('waitingListAccountFlag', WaitingListAccountFlag::class);
        Route::model('trainingPosition', TrainingPosition::class);

        if ($this->app->routesAreCached()) {
            return;
        }

        Route::middleware(['nova', SubstituteBindings::class])
                ->prefix('nova-vendor/waiting-lists-manager')
                ->group(__DIR__.'/../routes/api.php');
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
