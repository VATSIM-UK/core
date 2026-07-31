<?php

namespace App\Filament\Training\Resources\Seminars\Pages;

use App\Filament\Training\Resources\Seminars\SeminarResource;
use Filament\Resources\Pages\EditRecord;

class EditSeminar extends EditRecord
{
    protected static string $resource = SeminarResource::class;

    public static function canAccess(): bool
    {
        return auth()->user()->can('training.seminars.manage.*');
    }
}
