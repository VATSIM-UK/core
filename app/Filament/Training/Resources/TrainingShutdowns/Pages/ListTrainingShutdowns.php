<?php

namespace App\Filament\Training\Resources\TrainingShutdowns\Pages;

use App\Filament\Training\Resources\TrainingShutdowns\TrainingShutdownResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListTrainingShutdowns extends ListRecords
{
    protected static string $resource = TrainingShutdownResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
