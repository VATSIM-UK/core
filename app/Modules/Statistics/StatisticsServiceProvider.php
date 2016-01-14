<?php

namespace App\Modules\Statistics;

use Route;
use Auth;
use Request;
use Response;
use Redirect;
use Session;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;

class StatisticsServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to the controller routes in the routes file.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Modules\Statistics\Http\Controllers';

    protected $listen = [
        "App\Modules\Statistics\Events\AtcSessionStarted" => [

        ],
        "App\Modules\Statistics\Events\AtcSessionEnded" => [

        ],
    ];

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        if(!$this->app->routesAreCached()){
            require __DIR__."/Http/routes.php";
        }

        $this->publishes([
            __DIR__.'/Database/Factories/' => database_path('factories')
        ]);

        $this->publishes([
            __DIR__.'/Database/Migrations/' => database_path('migrations')
        ]);

        $this->publishes([
            __DIR__.'/Config/statistics.php' => config_path('package.php')
        ]);
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        // TODO: Implement register() method.
    }
}
