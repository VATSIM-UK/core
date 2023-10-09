<?php

namespace App\Filament\Resources\AccountResource\Pages;

use App\Filament\Helpers\Pages\BaseEditRecordPage;
use App\Filament\Resources\AccountResource;
use Filament\Pages\Actions;

class EditAccount extends BaseEditRecordPage
{
    protected static string $resource = AccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(),
        ];
    }
}
