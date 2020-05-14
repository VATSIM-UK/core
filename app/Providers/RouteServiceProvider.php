<?php

namespace App\Providers;

use App\Models\TeamSpeak\Registration;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Redirect;
use Route;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

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
        Route::model('mshipAccount', \App\Models\Mship\Account::class, function () {
            return Redirect::route('adm.mship.account.index')->withError('The account ID you provided was not found.');
        });

        Route::model('mshipAccountEmail', \App\Models\Mship\Account\Email::class);

        Route::bind('mshipRegistration', function ($value) {
            return Auth::user()->teamspeakRegistrations()->findOrFail($value);
        });


        Route::model('ban', \App\Models\Mship\Account\Ban::class, function () {
            return Redirect::route('adm.mship.account.index')->withError('The ban ID you provided was not found.');
        });
        Route::model('ssoEmail', \App\Models\Sso\Email::class);
        Route::model('sysNotification', \App\Models\Sys\Notification::class);

        Route::model('mshipRole', Role::class, function () {
            Redirect::route('adm.mship.role.index')->withError('Role doesn\'t exist.');
        });

        Route::model('mshipPermission', Permission::class, function () {
            Redirect::route('adm.mship.permission.index')->withError('Permission doesn\'t exist.');
        });

        Route::model('mshipNoteType', \App\Models\Mship\Note\Type::class, function () {
            Redirect::route('adm.mship.note.type.index')->withError("Note type doesn't exist.");
        });

        Route::bind('applicationByPublicId', function ($value) {
            return \App\Models\VisitTransfer\Application::findByPublicId($value);
        });

        Route::bind('ukAirportByICAO', function ($value) {
            return \App\Models\Airport::uk()->icao($value)->first() ?? abort(404);
        });
    }
}
