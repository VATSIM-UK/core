<?php

namespace App\Filament\Training\Resources\PositionGroups\Pages;

use App\Filament\Training\Resources\PositionGroups\PositionGroupResource;
use Filament\Resources\Pages\ListRecords;

class ListPositionGroups extends ListRecords
{
    protected static string $resource = PositionGroupResource::class;

    public static function canAccess(array $parameters = []): bool
    {
        return false;
    }
}
