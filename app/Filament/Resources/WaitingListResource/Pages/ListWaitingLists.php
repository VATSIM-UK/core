<?php

namespace App\Filament\Resources\WaitingListResource\Pages;

use App\Filament\Helpers\Pages\BaseListRecordsPage;
use App\Filament\Resources\WaitingListResource;
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
