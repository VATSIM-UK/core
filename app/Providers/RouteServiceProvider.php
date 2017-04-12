<?php

namespace App\Providers;

use Route;
use Redirect;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to the controller routes in your routes file.
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

        // Route Model Bindings
        Route::model('mshipAccount', \App\Models\Mship\Account::class, function () {
            return Redirect::route('adm.mship.account.index')->withError('The account ID you provided was not found.');
        });

        Route::model('ban', \App\Models\Mship\Account\Ban::class, function () {
            return Redirect::route('adm.mship.account.index')->withError('The ban ID you provided was not found.');
        });

        Route::model('mshipAccountEmail', \App\Models\Mship\Account\Email::class);
        Route::model('ssoEmail', \App\Models\Sso\Email::class);
        Route::model('sysNotification', \App\Models\Sys\Notification::class);

        Route::model('mshipRole', \App\Models\Mship\Role::class, function () {
            Redirect::route('adm.mship.role.index')->withError('Role doesn\'t exist.');
        });

        Route::model('mshipPermission', \App\Models\Mship\Permission::class, function () {
            Redirect::route('adm.mship.permission.index')->withError('Permission doesn\'t exist.');
        });

        Route::model('mshipNoteType', \App\Models\Mship\Note\Type::class, function () {
            Redirect::route('adm.mship.note.type.index')->withError("Note type doesn't exist.");
        });
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
        Route::group([
            'middleware' => 'web',
            'namespace' => $this->namespace,
        ], function ($router) {
            require base_path('routes/web.php');
        });
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
        Route::group([
            'middleware' => 'api',
            'namespace' => $this->namespace,
            'prefix' => 'api',
        ], function ($router) {
            require base_path('routes/api.php');
        });
    }
}
