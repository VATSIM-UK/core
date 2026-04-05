<?php

namespace App\Filament\Admin\Resources\EndorsementRequestResource\Pages;

use App\Filament\Admin\Resources\EndorsementRequestResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEndorsementRequests extends ListRecords
{
    protected static string $resource = EndorsementRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
