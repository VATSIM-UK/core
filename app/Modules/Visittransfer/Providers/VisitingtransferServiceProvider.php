<?php
namespace App\Modules\Visittransfer\Providers;

use App;
use Config;
use Lang;
use View;
use Illuminate\Support\ServiceProvider;

class VisittransferServiceProvider extends ServiceProvider
{
	/**
	 * Register the Visittransfer module service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		// This service provider is a convenient place to register your modules
		// services in the IoC container. If you wish, you may make additional
		// methods or service providers to keep the code more focused and granular.
		App::register('App\Modules\Visittransfer\Providers\RouteServiceProvider');

		$this->registerNamespaces();
	}

	/**
	 * Register the Visittransfer module resource namespaces.
	 *
	 * @return void
	 */
	protected function registerNamespaces()
	{
		Lang::addNamespace('visittransfer', realpath(__DIR__.'/../Resources/Lang'));

		View::addNamespace('visittransfer', base_path('resources/views/vendor/visittransfer'));
		View::addNamespace('visittransfer', realpath(__DIR__.'/../Resources/Views'));
	}
}
