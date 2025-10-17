<?php

namespace App\Filament\Admin\Resources\EndorsementRequestResource\Pages;

use App\Filament\Admin\Resources\EndorsementRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListEndorsementRequests extends ListRecords
{
    protected static string $resource = EndorsementRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
