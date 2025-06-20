<?php

namespace App\Filament\Resources\TheoryManagementResource\Pages;

use App\Filament\Resources\TheoryManagementResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTheoryManagement extends ListRecords
{
    protected static string $resource = TheoryManagementResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //Actions\CreateAction::make(),
        ];
    }
}
