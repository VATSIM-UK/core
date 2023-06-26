<?php

namespace App\Filament\Helpers\ResourceManagers;

use Filament\Tables;

trait MakesExternalViewButtons
{
    /**
     * Creates a view action button to view a resource on it's page
     *
     * @param  string  $modelName The singular model name, used as the slug
     * @return void
     */
    protected static function resourceViewAction(string $resource)
    {
        return Tables\Actions\ViewAction::make()
            ->url(fn ($record) => $resource::urlToView($record));
    }
}
