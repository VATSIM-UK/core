<?php

namespace App\Filament\Admin\Resources\Positions\Pages;

use App\Filament\Admin\Helpers\Pages\BaseListRecordsPage;
use App\Filament\Admin\Resources\Positions\PositionResource;
use Filament\Actions\CreateAction;

class ListPositions extends BaseListRecordsPage
{
    protected static string $resource = PositionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
