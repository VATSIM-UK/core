<?php

namespace App\Filament\Resources\TheoryManagementResource\Pages;

use App\Filament\Resources\TheoryManagementResource;
// use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTheoryManagement extends EditRecord
{
    protected static string $resource = TheoryManagementResource::class;

    protected function getFormActions(): array
    {
        return [];
    }

    /*protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }*/
}
