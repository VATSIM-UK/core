<?php

namespace App\Filament\Training\Resources\TrainingPlaceResource\Pages;

use App\Filament\Training\Resources\TrainingPlaceResource;
use Filament\Resources\Pages\ListRecords;

class ListTrainingPlaces extends ListRecords
{
    protected static string $resource = TrainingPlaceResource::class;

    protected static ?string $title = 'Training Places';
}
