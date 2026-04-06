<?php

namespace App\Filament\Training\Resources\WaitingLists\Pages;

use App\Filament\Admin\Helpers\Pages\BaseListRecordsPage;
use App\Filament\Training\Resources\WaitingLists\WaitingListResource;
use Filament\Actions\CreateAction;

class ListWaitingLists extends BaseListRecordsPage
{
    protected static string $resource = WaitingListResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
