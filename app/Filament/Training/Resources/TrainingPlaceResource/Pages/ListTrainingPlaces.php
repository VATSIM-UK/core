<?php

namespace App\Filament\Training\Resources\TrainingPlaceResource\Pages;

use App\Filament\Training\Resources\TrainingPlaceResource;
use App\Filament\Training\Resources\TrainingPlaceResource\Widgets\TrainingPlaceCategoryChart;
use App\Filament\Training\Resources\TrainingPlaceResource\Widgets\TrainingPlaceOffersOverview;
use Filament\Resources\Pages\ListRecords;

class ListTrainingPlaces extends ListRecords
{
    protected static string $resource = TrainingPlaceResource::class;

    protected static ?string $title = 'Training Places';

    protected function getHeaderWidgets(): array
    {
        return [
            TrainingPlaceCategoryChart::class,
        ];
    }

    protected function getFooterWidgets(): array
    {
        return [
            TrainingPlaceOffersOverview::class,
        ];
    }
}
