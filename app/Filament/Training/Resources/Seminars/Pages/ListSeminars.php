<?php

namespace App\Filament\Training\Resources\Seminars\Pages;

use App\Filament\Training\Resources\Seminars\SeminarResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListSeminars extends ListRecords
{
    protected static string $resource = SeminarResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
