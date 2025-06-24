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

    public function getTitle(): string {
        $level = strtoupper(str_replace('theory_', '', $this->record->item));
        return "Edit {$level} Questions";
    }

    /*protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }*/
}
