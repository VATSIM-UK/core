<?php

namespace App\Filament\Admin\Resources\Events\Pages;

use App\Filament\Admin\Helpers\Pages\BaseListRecordsPage;
use App\Filament\Admin\Resources\Events\EventResource;
use Filament\Actions\CreateAction;

class ListEvents extends BaseListRecordsPage
{
    protected static string $resource = EventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
