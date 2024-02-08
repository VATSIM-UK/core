<?php

namespace App\Providers;

use App\Livewire\Roster\Index;
use App\Livewire\Roster\Renew;
use App\Livewire\Roster\Search;
use App\Livewire\Roster\Show;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        $this->registerRouteModelBindings();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapWebRoutes();
        $this->mapApiRoutes();
    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    protected function mapWebRoutes()
    {
        Route::middleware('web')
            ->namespace($this->namespace)
            ->group(base_path('routes/web.php'));

        Route::get('/roster', Index::class)->name('site.roster.index');
        Route::get('/roster/renew', Renew::class)->name('site.roster.renew');
        Route::get('/roster/search', Search::class)->name('site.roster.search');
        Route::get('/roster/{account}', Show::class)->name('site.roster.show');
    }

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('api')
            ->middleware('api')
            ->namespace($this->namespace)
            ->group(base_path('routes/api.php'));
    }

    private function registerRouteModelBindings()
    {
        Route::bind('mshipRegistration', function ($value) {
            return Auth::user()->teamspeakRegistrations()->findOrFail($value);
        });

        Route::model('ssoEmail', \App\Models\Sso\Email::class);
        Route::model('sysNotification', \App\Models\Sys\Notification::class);

        Route::bind('applicationByPublicId', function ($value) {
            return \App\Models\VisitTransfer\Application::findByPublicId($value);
        });

        Route::bind('ukAirportByICAO', function ($value) {
            return \App\Models\Airport::uk()->icao($value)->first() ?? abort(404);
        });
    }
}
