<?php

namespace App\Filament\Admin\Resources\VisitTransfer\FacilityResource\Pages;

use App\Filament\Admin\Resources\VisitTransfer\FacilityResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditFacility extends EditRecord
{
    protected static string $resource = FacilityResource::class;

    protected function getHeaderActions(): array
    {
        return [

        ];
    }
}
