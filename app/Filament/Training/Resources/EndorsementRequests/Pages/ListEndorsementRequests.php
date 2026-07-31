<?php

namespace App\Filament\Training\Resources\EndorsementRequests\Pages;

use App\Filament\Training\Resources\EndorsementRequests\EndorsementRequestResource;
use Filament\Resources\Pages\ListRecords;

class ListEndorsementRequests extends ListRecords
{
    protected static string $resource = EndorsementRequestResource::class;

    public static function canAccess(array $parameters = []): bool
    {
        return false;
    }
}
