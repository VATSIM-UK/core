<?php

namespace App\Filament\Resources\AccountResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;

class StatesRelationManager extends RelationManager
{
    protected static string $relationship = 'states';

    protected static ?string $recordTitleAttribute = 'id';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
                Tables\Columns\TextColumn::make('pivot.region')->label('Region'),
                Tables\Columns\TextColumn::make('pivot.division')->label('Divison'),
                Tables\Columns\TextColumn::make('start_at')->label('Start')->dateTime()->since()->description(fn ($record) => $record->start_at)->sortable(),
                Tables\Columns\TextColumn::make('end_at')->label('End')->dateTime()->since()->description(fn ($record) => $record->end_at),
            ])->defaultSort('start_at', 'asc');
    }
}
