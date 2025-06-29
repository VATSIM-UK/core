<?php

namespace App\Filament\Resources\RosterRestrictionResource\Pages;

use App\Filament\Resources\RosterRestrictionResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListRosterRestrictions extends ListRecords
{
    protected static string $resource = RosterRestrictionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
