<?php

namespace App\Filament\Resources\TheoryResultResource\Pages;

use App\Filament\Resources\TheoryResultResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditTheoryResult extends EditRecord
{
    protected static string $resource = TheoryResultResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
