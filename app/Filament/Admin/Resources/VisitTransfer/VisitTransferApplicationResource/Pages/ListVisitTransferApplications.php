<?php

namespace App\Filament\Admin\Resources\VisitTransfer\VisitTransferApplicationResource\Pages;

use App\Filament\Admin\Resources\VisitTransfer\VisitTransferApplicationResource;
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
        return parent::getTableQuery()->where('type', $this->type);
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
                    ->url(fn () => static::getResource()::getUrl('index', ['type' => $this->type])),

                Action::make('open')
                    ->label('Open Applications')
                    ->url(fn () => static::getResource()::getUrl('index', ['type' => $this->type, 'tableFilters' => ['status' => ['values' => [0 => Application::STATUS_SUBMITTED]]]])),

                Action::make('review')
                    ->label('Review Applications')
                    ->url(fn () => static::getResource()::getUrl('index', ['type' => $this->type, 'tableFilters' => ['status' => ['values' => [0 => Application::STATUS_UNDER_REVIEW]]]])),
                Action::make('accepted')
                    ->label('Accepted Applications')
                    ->url(fn () => static::getResource()::getUrl('index', ['type' => $this->type, 'tableFilters' => ['status' => ['values' => [0 => Application::STATUS_ACCEPTED, 1 => Application::STATUS_IN_PROGRESS]]]])),

                Action::make('closed')
                    ->label('Closed Applications')
                    ->url(fn () => static::getResource()::getUrl('index', ['type' => $this->type, 'tableFilters' => ['status' => ['values' => [0 => Application::STATUS_COMPLETED, 1 => Application::STATUS_REJECTED, 2 => Application::STATUS_WITHDRAWN, 3 => Application::STATUS_EXPIRED, 4 => Application::STATUS_CANCELLED, 5 => Application::STATUS_LAPSED]]]])),

            ])->label('Status')->button()->icon('heroicon-o-flag')->color('gray'),
        ];
    }

    public function getTitle(): string
    {
        return ($this->type === Application::TYPE_TRANSFER ? 'Transferring' : 'Visiting').' Applications';
    }
}
