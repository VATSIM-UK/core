<?php

namespace App\Filament\Training\Resources\WaitingLists\Pages;

use App\Filament\Training\Resources\WaitingLists\WaitingListResource;
use Filament\Resources\Pages\CreateRecord;

class CreateWaitingList extends CreateRecord
{
    protected static string $resource = WaitingListResource::class;
}
