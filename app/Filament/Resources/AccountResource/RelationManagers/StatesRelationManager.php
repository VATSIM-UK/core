<?php

namespace App\Filament\Resources\AccountResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class StatesRelationManager extends RelationManager
{
    protected static string $relationship = 'statesHistory';

    protected static ?string $recordTitleAttribute = 'id';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('status')->badge()->getStateUsing(function ($record) {
                    if ($record->pivot->end_at) {
                        return 'Old';
                    }

                    return 'Active - '.($record->is_permanent ? 'Permenant' : 'Temporary');
                })->color(fn (string $state): string => match ($state) {
                    'Old' => 'gray',
                    'Active - Permenant' => 'success',
                    'Active - Temporary' => 'success',
                }),
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('pivot.region')->label('Region'),
                Tables\Columns\TextColumn::make('pivot.division')->label('Divison'),
                Tables\Columns\TextColumn::make('start_at')->label('Start')->dateTime()->since()->description(fn ($record) => $record->start_at)->sortable(),
                Tables\Columns\TextColumn::make('end_at')->label('End')->dateTime()->since()->description(fn ($record) => $record->end_at),
            ])->defaultSort('start_at', 'asc');
    }
}
