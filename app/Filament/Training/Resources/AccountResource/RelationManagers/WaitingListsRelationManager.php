<?php

namespace App\Filament\Training\Resources\AccountResource\RelationManagers;

use App\Filament\Training\Resources\WaitingListResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

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
            ->modifyQueryUsing(fn (\Illuminate\Database\Eloquent\Builder $query) => $query->with('waitingList'))
            ->recordTitle(fn ($record) => $record->waitingList?->name ? ('Waiting List: '.$record->waitingList->name) : "Waiting List #{$record->id}")
            ->columns([
                Tables\Columns\TextColumn::make('waitingList.name')->label('Waiting List')->sortable(),
                Tables\Columns\TextColumn::make('waitingList.formatted_department')->label('Department')->sortable(),
                Tables\Columns\TextColumn::make('position')->label('Position')->getStateUsing(fn ($record) => $record->position ?? '-'),
                Tables\Columns\TextColumn::make('created_at')->label('Added On')->date()->sortable(),
            ])
            ->filters([
                Tables\Filters\TrashedFilter::make(),
                Tables\Filters\SelectFilter::make('waiting_list_id')
                    ->label('Waiting List')
                    ->relationship('waitingList', 'name')
                    ->multiple()
                    ->placeholder('Any'),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn ($record) => WaitingListResource::getUrl('view', [
                        'record' => $record->waitingList,
                    ])),
            ])
            ->bulkActions([])
            ->headerActions([]);
    }
}
