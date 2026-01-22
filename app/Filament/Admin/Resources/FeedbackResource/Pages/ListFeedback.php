<?php

namespace App\Filament\Admin\Resources\FeedbackResource\Pages;

use App\Filament\Admin\Helpers\Pages\BaseListRecordsPage;
use App\Filament\Admin\Resources\FeedbackResource;
use App\Filament\Admin\Resources\FeedbackResource\Widgets\FeedbackOverview;
use Filament\Resources\Components\Tab;
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

    public function getTabs(): array
    {
        return [
            Tab::make('Active')
                ->modifyQueryUsing(fn (Builder $query) => $query->whereNull('deleted_at')),
            Tab::make('Rejected')
                ->modifyQueryUsing(fn (Builder $query) => $query->onlyTrashed()),
        ];
    }
}
