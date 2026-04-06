<?php

namespace App\Filament\Admin\Resources\Accounts\Pages;

use App\Filament\Admin\Helpers\Pages\BaseEditRecordPage;
use App\Filament\Admin\Resources\Accounts\AccountResource;
use Filament\Actions\ViewAction;

class EditAccount extends BaseEditRecordPage
{
    protected static string $resource = AccountResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
        ];
    }
}
