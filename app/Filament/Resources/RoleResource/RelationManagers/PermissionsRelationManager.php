<?php

namespace App\Filament\Resources\RoleResource\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;

class PermissionsRelationManager extends RelationManager
{
    protected static string $relationship = 'permissions';

    protected static ?string $recordTitleAttribute = 'name';

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()->inverseRelationshipName('roles')->preloadRecordSelect()->label('Attach')->color('primary'),
            ])
            ->actions([
                Tables\Actions\DetachAction::make()->inverseRelationshipName('roles')->label('Detach'),
            ])
            ->bulkActions([
                Tables\Actions\DetachBulkAction::make()->inverseRelationshipName('roles')->label('Detach'),
            ]);
    }
}
