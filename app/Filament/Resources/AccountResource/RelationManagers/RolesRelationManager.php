<?php

namespace App\Filament\Resources\AccountResource\RelationManagers;

use App\Filament\Resources\RoleResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;

class RolesRelationManager extends RelationManager
{
    protected static string $relationship = 'roles';

    protected static ?string $recordTitleAttribute = 'name';

    protected static ?string $inverseRelationship = 'users';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name'),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()->preloadRecordSelect()->label('Add / Attach')->color('primary'),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->resource(RoleResource::class),
                Tables\Actions\DetachAction::make()->label('Remove'),
            ]);
    }
}
