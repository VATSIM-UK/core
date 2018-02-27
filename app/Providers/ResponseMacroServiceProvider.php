<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Response;

class ResponseMacroServiceProvider extends ServiceProvider
{
    /**
     * Register the application's response macros.
     *
     * @return void
     */
    public function boot()
    {
        Response::macro('csv', function ($value) {
            return Response::make(implode(',', $value));
        });

        Response::macro('psv', function ($value) {
            return Response::make(implode('|', $value));
        });
    }
}
