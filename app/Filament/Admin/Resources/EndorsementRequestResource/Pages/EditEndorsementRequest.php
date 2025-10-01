<?php

namespace App\Filament\Admin\Resources\EndorsementRequestResource\Pages;

use App\Filament\Admin\Resources\EndorsementRequestResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditEndorsementRequest extends EditRecord
{
    protected static string $resource = EndorsementRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
