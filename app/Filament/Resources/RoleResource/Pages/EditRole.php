<?php

namespace App\Filament\Resources\RoleResource\Pages;

use App\Filament\Helpers\Pages\BaseEditRecordPage;
use App\Filament\Resources\RoleResource;
use Filament\Pages\Actions;

class EditRole extends BaseEditRecordPage
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
