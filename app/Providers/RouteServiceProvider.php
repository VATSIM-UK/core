<?php

namespace App\Providers;

use Route;
use Auth;
use Request;
use Response;
use Redirect;
use Session;
use Illuminate\Routing\Router;
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
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    public function boot(Router $router)
    {
        parent::boot($router);

        // Route Model Bindings
        $router->model('mshipAccount', \App\Models\Mship\Account::class, function () {
            return Redirect::route('adm.mship.account.index')->withError('The account ID you provided was not found.');
        });

        $router->model('ban', \App\Models\Mship\Account\Ban::class, function() {
            return Redirect::route('adm.mship.account.index')->withError('The ban ID you provided was not found.');
        });

        $router->model('mshipAccountEmail', \App\Models\Mship\Account\Email::class);
        $router->model('ssoEmail', \App\Models\Sso\Email::class);
        $router->model('sysNotification', \App\Models\Sys\Notification::class);

        $router->model('mshipRole', \App\Models\Mship\Role::class, function () {
            Redirect::route('adm.mship.role.index')->withError('Role doesn\'t exist.');
        });

        $router->model('mshipPermission', \App\Models\Mship\Permission::class, function () {
            Redirect::route('adm.mship.permission.index')->withError('Permission doesn\'t exist.');
        });

        $router->model("mshipNoteType", \App\Models\Mship\Note\Type::class, function(){
            Redirect::route("adm.mship.note.type.index")->withError("Note type doesn't exist.");
        });
    }

    /**
     * Define the routes for the application.
     *
     * @param  \Illuminate\Routing\Router  $router
     * @return void
     */
    public function map(Router $router)
    {
        $router->group(['namespace' => $this->namespace], function ($router) {
            require app_path('Http/routes.php');
        });
    }
}
