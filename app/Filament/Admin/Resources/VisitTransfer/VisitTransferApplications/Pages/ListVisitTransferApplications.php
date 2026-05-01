<?php

namespace App\Filament\Admin\Resources\VisitTransfer\VisitTransferApplications\Pages;

use App\Filament\Admin\Resources\VisitTransfer\VisitTransferApplications\VisitTransferApplicationResource;
use App\Models\VisitTransfer\Application;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Attributes\Url;

class ListVisitTransferApplications extends ListRecords
{
    protected static string $resource = VisitTransferApplicationResource::class;

    #[Url]
    public int $type = Application::TYPE_TRANSFER;

    protected function getTableQuery(): Builder
    {
        return parent::getTableQuery()->where('type', $this->type)
            ->whereNotIn('status', [Application::STATUS_WITHDRAWN, Application::STATUS_LAPSED, Application::STATUS_EXPIRED]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('transferring_applications')
                ->label('Transferring Applications')
                ->url(fn () => static::getResource()::getUrl('index', ['type' => Application::TYPE_TRANSFER, 'tableFilters' => request()->input('tableFilters', [])]))
                ->color($this->type === Application::TYPE_TRANSFER ? 'primary' : 'secondary'),

            Action::make('visiting_applications')
                ->label('Visiting Applications')
                ->url(fn () => static::getResource()::getUrl('index', ['type' => Application::TYPE_VISIT, 'tableFilters' => request()->input('tableFilters', [])]))
                ->color($this->type === Application::TYPE_VISIT ? 'primary' : 'secondary'),

            ActionGroup::make([

                Action::make('all')
                    ->label('All Applications')
                    ->action(function () {
                        $this->tableFilters['status'] = null;
                    }),

                Action::make('review')
                    ->label('Review Applications')
                    ->action(function () {
                        $this->tableFilters['status'] = ['values' => [Application::STATUS_UNDER_REVIEW, Application::STATUS_SUBMITTED]];
                    }),

                Action::make('accepted')
                    ->label('Accepted Applications')
                    ->action(function () {
                        $this->tableFilters['status'] = ['values' => [0 => Application::STATUS_ACCEPTED, 1 => Application::STATUS_IN_PROGRESS]];
                    }),

                Action::make('closed')
                    ->label('Closed Applications')
                    ->action(function () {
                        $this->tableFilters['status'] = ['values' => [0 => Application::STATUS_COMPLETED, 1 => Application::STATUS_REJECTED,  2 => Application::STATUS_CANCELLED]];
                    }),

            ])->label('Status')->button()->icon('heroicon-o-flag')->color('gray'),
        ];
    }

    public function getTitle(): string
    {
        return ($this->type === Application::TYPE_TRANSFER ? 'Transferring' : 'Visiting').' Applications';
    }
}
