<?php

namespace App\Filament\Training\Resources;

use App\Filament\Training\Pages\TrainingPlace\ViewTrainingPlace;
use App\Filament\Training\Resources\TrainingPlaceResource\Pages;
use App\Models\Training\TrainingPlace\TrainingPlace;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TrainingPlaceResource extends Resource
{
    protected static ?string $model = TrainingPlace::class;

    protected static ?string $navigationIcon = 'heroicon-o-academic-cap';

    protected static ?string $navigationLabel = 'Training Places';

    protected static ?string $navigationGroup = 'Training';

    protected static ?int $navigationSort = 2;

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query
                ->with([
                    'waitingListAccount.account',
                    'waitingListAccount.waitingList',
                    'trainingPosition.position',
                ])
            )
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('waitingListAccount.account.name')
                    ->label('Student')
                    ->searchable(['name_first', 'name_last'])
                    ->sortable()
                    ->url(fn (TrainingPlace $record) => ViewTrainingPlace::getUrl(['trainingPlaceId' => $record->id])),

                Tables\Columns\TextColumn::make('waitingListAccount.account_id')
                    ->label('CID')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('trainingPosition.position.callsign')
                    ->label('Position')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('waiting_list')
                    ->relationship('waitingListAccount.waitingList', 'name')
                    ->label('Waiting List')
                    ->preload()
                    ->searchable(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->url(fn (TrainingPlace $record) => url("/training/training-places/{$record->id}")),
            ])
            ->defaultSort('created_at', 'desc')
            ->persistSearchInSession()
            ->persistFiltersInSession()
            ->persistColumnSearchesInSession()
            ->paginated(['25', '50', '100'])
            ->defaultPaginationPageOption(25);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTrainingPlaces::route('/'),
        ];
    }
}
