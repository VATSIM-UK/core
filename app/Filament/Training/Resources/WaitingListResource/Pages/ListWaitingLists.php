<?php

namespace App\Filament\Training\Resources\WaitingListResource\Pages;

use App\Filament\Admin\Helpers\Pages\BaseListRecordsPage;
use App\Filament\Training\Resources\WaitingListResource;
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
