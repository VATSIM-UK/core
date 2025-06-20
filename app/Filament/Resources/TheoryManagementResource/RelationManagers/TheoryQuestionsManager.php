<?php

namespace App\Filament\Resources\TheoryManagementResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Illuminate\Database\Eloquent\Builder;
//use Illuminate\Database\Eloquent\Relations\Relation;

class TheoryQuestionsManager extends RelationManager
{
    protected static string $relationship = 'questions';

    protected function getTableQuery(): Builder
    {
        return $this->ownerRecord->questions()->getQuery()->where('deleted', 0);
    }

    public function table(Tables\Table $table): Tables\Table
    {
        return $table
            // ->recordTitleAttribute('name')
            ->columns([
                Tables\Columns\TextColumn::make('id')->searchable(),
                Tables\Columns\TextColumn::make('question')->searchable(),
                // Tables\Columns\TextColumn::make('option_1'),
                // Tables\Columns\TextColumn::make('option_2'),
                // Tables\Columns\TextColumn::make('option_3'),
                // Tables\Columns\TextColumn::make('option_4'),
                // Tables\Columns\TextColumn::make('answer'),
                // Tables\Columns\TextColumn::make('add_by'),
                // Tables\Columns\TextColumn::make('add_date'),
                // Tables\Columns\TextColumn::make('edit_by'),
                // Tables\Columns\TextColumn::make('edit_date'),
                // Tables\Columns\TextColumn::make('status'),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ]);
    }
}
