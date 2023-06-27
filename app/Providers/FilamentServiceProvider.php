<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;

class FilamentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerMacros();
    }

    protected function registerMacros(): void
    {
        /**
         * Returns a URL to view the resource
         */
        \Filament\Resources\Resource::macro('urlToView', function (?Model $record): ?string {
            /** @var \Filament\Resources\Resource $this */
            if (! $record) {
                return null;
            }

            return static::getUrl(static::hasPage('view') ? 'view' : 'edit', ['record' => $record]);
        });

        /**
         * Configures a table View action to route to the resource's view page
         */
        \Filament\Tables\Actions\ViewAction::macro('resource', function (string $resourceClass): \Filament\Tables\Actions\ViewAction {
            /** @var \Filament\Tables\Actions\ViewAction $this */
            return $this->url(fn ($record) => $resourceClass::urlToView($record));
        });
    }
}
