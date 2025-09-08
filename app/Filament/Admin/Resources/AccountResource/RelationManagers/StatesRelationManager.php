<?php

namespace App\Filament\Admin\Resources\AccountResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StatesRelationManager extends RelationManager
{
    protected static string $relationship = 'statesHistory';

    protected static ?string $recordTitleAttribute = 'id';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('status')->badge()->getStateUsing(function ($record) {
                    if ($record->pivot->end_at) {
                        return 'Old';
                    }

                    return 'Active - '.($record->is_permanent ? 'Permenant' : 'Temporary');
                })->color(fn (string $state): string => match ($state) {
                    'Old' => 'gray',
                    'Active - Permenant' => 'success',
                    'Active - Temporary' => 'success',
                }),
                TextColumn::make('name'),
                TextColumn::make('pivot.region')->label('Region'),
                TextColumn::make('pivot.division')->label('Divison'),
                TextColumn::make('start_at')->label('Start')->dateTime()->since()->description(fn ($record) => $record->start_at)->sortable(),
                TextColumn::make('end_at')->label('End')->dateTime()->since()->description(fn ($record) => $record->end_at),
            ])->defaultSort('start_at', 'asc');
    }
}
