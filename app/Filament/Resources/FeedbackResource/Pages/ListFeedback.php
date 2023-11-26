<?php

namespace App\Filament\Resources\FeedbackResource\Pages;

use App\Filament\Helpers\Pages\BaseListRecordsPage;
use App\Filament\Resources\FeedbackResource;
use App\Filament\Resources\FeedbackResource\Widgets\FeedbackOverview;
use Illuminate\Database\Eloquent\Builder;

class ListFeedback extends BaseListRecordsPage
{
    protected static string $resource = FeedbackResource::class;

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()->with(['account', 'submitter', 'form']);
    }

    protected function getHeaderActions(): array
    {
        return [];
    }

    protected function getHeaderWidgets(): array
    {
        return [
            FeedbackOverview::class,
        ];
    }

    protected function getTableRecordsPerPageSelectOptions(): array
    {
        return [25, 50, 75, 100];
    }
}
