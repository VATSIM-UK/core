<?php

namespace App\Filament\Admin\Resources\WaitingListResource\Pages;

use App\Filament\Admin\Helpers\Pages\BaseListRecordsPage;
use App\Filament\Admin\Resources\WaitingListResource;
use Filament\Actions;

class ListWaitingLists extends BaseListRecordsPage
{
    protected static string $resource = WaitingListResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
