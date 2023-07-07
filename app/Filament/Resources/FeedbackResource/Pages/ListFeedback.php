<?php

namespace App\Filament\Resources\FeedbackResource\Pages;

use App\Filament\Resources\FeedbackResource;
use App\Filament\Resources\FeedbackResource\Widgets\FeedbackOverview;
use Filament\Pages\Actions;
use Filament\Resources\Pages\ListRecords;

class ListFeedback extends ListRecords
{
    protected static string $resource = FeedbackResource::class;

    protected function getActions(): array
    {
        return [];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            FeedbackOverview::class
        ];
    }
}
