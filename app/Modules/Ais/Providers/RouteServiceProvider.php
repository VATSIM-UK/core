<?php

namespace App\Modules\Ais\Providers;

use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The controller namespace for the module.
     *
     * @var string|null
     */
    protected $namespace = 'App\Modules\Ais\Http\Controllers';

    /**
     * Define your module's route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }

    /**
     * Define the routes for the module.
     *
     * @return void
     */
    public function map()
    {
        Route::group([
            'namespace'  => $this->namespace,
            'middleware' => ['web']
        ], function ($router) {
            require(config('modules.path').'/Ais/Http/routes.php');
        });
    }
}
