<?php

namespace App\Filament\Admin\Resources\AccountResource\RelationManagers;

use App\Filament\Admin\Resources\WaitingListResource;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class WaitingListsRelationManager extends RelationManager
{
    protected static string $relationship = 'waitingListAccounts';

    protected static ?string $recordTitleAttribute = 'id';

    protected static ?string $title = 'Waiting Lists';

    protected function getLogActionName(): string
    {
        return 'ViewWaitingLists';
    }

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('waitingList'))
            ->recordTitle(fn ($record) => $record->waitingList?->name ? ('Waiting List: '.$record->waitingList->name) : "Waiting List #{$record->id}")
            ->columns([
                TextColumn::make('waitingList.name')->label('Waiting List')->sortable(),
                TextColumn::make('waitingList.formatted_department')->label('Department')->sortable(),
                TextColumn::make('position')->label('Position')->getStateUsing(fn ($record) => $record->position ?? '-'),
                TextColumn::make('created_at')->label('Added On')->date()->sortable(),
            ])
            ->filters([
                TrashedFilter::make(),
                SelectFilter::make('waiting_list_id')
                    ->label('Waiting List')
                    ->relationship('waitingList', 'name')
                    ->multiple()
                    ->placeholder('Any'),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make()
                    ->url(fn ($record) => WaitingListResource::getUrl('view', [
                        'record' => $record->waitingList,
                    ])),
            ])
            ->toolbarActions([])
            ->headerActions([]);
    }
}
