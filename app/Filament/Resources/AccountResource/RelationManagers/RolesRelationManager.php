<?php

namespace App\Filament\Resources\AccountResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Tables;

class RolesRelationManager extends RelationManager
{
    protected static string $relationship = 'roles';

    protected static ?string $recordTitleAttribute = 'name';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()->inverseRelationshipName('users')->preloadRecordSelect()->label('Add / Attach')->color('primary'),
            ])
            ->actions([
                Tables\Actions\DetachAction::make()->inverseRelationshipName('users')->label('Remove'),
            ]);
    }
}
