<?php

namespace App\Filament\Resources\TheoryResultResource\Pages;

use App\Filament\Resources\TheoryResultResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListTheoryResults extends ListRecords
{
    protected static string $resource = TheoryResultResource::class;

    protected function getHeaderActions(): array
    {
        return [
            //Actions\CreateAction::make(),
        ];
    }
}
