<?php

namespace App\Providers;

use Filament\Forms\Components\Actions\Action;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

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
         * Configures a text column with a link which routes to the resource's view page
         */
        \Filament\Tables\Columns\TextColumn::macro('viewResource', function (string $resourceClass, string $attribute = null): \Filament\Tables\Columns\TextColumn {
            /** @var \Filament\Tables\Actions\TextColumn $this */
            $attribute = $attribute ?? explode('.', $this->getName())[0]; // We assume that the column name is like user or user.name - we want the first part to get the relation

            return $this
                ->icon(fn ($record) => $record->{$attribute} ? 'heroicon-o-eye' : null)
                ->url(fn ($record) => $resourceClass::urlToView($record->{$attribute}));
        });

        /**
         * Configures a table view action to route to the resource's view page
         */
        \Filament\Tables\Actions\ViewAction::macro('resource', function (string $resourceClass): \Filament\Tables\Actions\ViewAction {
            /** @var \Filament\Tables\Actions\ViewAction $this */
            return $this->url(fn ($record) => $resourceClass::urlToView($record));
        });

        /**
         * Defines a relationship that is linked to a resource
         */
        \Filament\Forms\Components\Select::macro('resourceRelationship', function (string $resourceClass, string $relationName = null, string $titleAttribute = null): \Filament\Forms\Components\Select {
            /** @var \Filament\Forms\Components\Select $this */
            $relationModelName = class_basename($resourceClass::getModel());

            // Define the relationship for the selection, using the relation name (guessed from the model if required)
            return $this->relationship(fn () => $relationName ?: Str::lower($this->isMultiple() ? Str::plural($relationModelName) : $relationModelName), $titleAttribute ?? $resourceClass::getRecordTitleAttribute())
                ->suffixAction(
                    function ($state, $context) use ($resourceClass) {
                        if ($state === null || $context !== 'view' || $this->isMultiple()) {
                            return null;
                        }

                        $relationship = $this->getRelationship()->getResults();

                        return Action::make('view resource')
                            ->icon('heroicon-o-external-link')
                            ->url($resourceClass::urlToView($relationship));
                    }
                );
        });
    }
}
