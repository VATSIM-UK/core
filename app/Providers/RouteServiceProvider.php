<?php

namespace App\Providers;

use Route;
use Auth;
use Request;
use Response;
use Redirect;
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
        Route::filter('auth.user', function() {
            if(!Auth::user()->check()){
                if(Request::ajax()){
                    return Response::make("Unauthorised", 401);
                } else {
                    return Redirect::to("/");
                }
            }
        });

        Route::filter('auth.user.full', function() {
            if(!Auth::user()->check() OR !Auth::user()->get()->auth_extra){
                if(Request::ajax()){
                    return Response::make("Unauthorised", 401);
                } else {
                    return Redirect::to("/mship/auth/redirect");
                }
            }
        });

        Route::filter("user.must.read.notifications", function(){
            if(Auth::user()->check() && Auth::user()->get()->auth_extra && (Auth::user()->get()->has_unread_important_notifications OR Auth::user()->get()->has_unread_must_acknowledge_notifications)){
                Session::set("force_notification_read_return_url", Request::fullUrl());
                return Redirect::route("mship.notification.list");
            }
        });

        Route::filter('auth.admin', function() {
            if(!Auth::admin()->check()){
                if(Request::ajax()){
                    return Response::make("Unauthorised", 401);
                } else {
                    return Redirect::route("adm.authentication.login");
                }
            } else {
                if(!Auth::admin()->get()->hasPermission(Request::decodedPath())){
                    return Redirect::route("adm.error", [401]);
                }
            }
        });

        parent::boot($router);
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
