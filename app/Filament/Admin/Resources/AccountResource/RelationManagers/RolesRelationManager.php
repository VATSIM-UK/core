<?php

namespace App\Filament\Admin\Resources\AccountResource\RelationManagers;

use App\Filament\Admin\Resources\RoleResource;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
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
                TextColumn::make('name'),
            ])
            ->headerActions([
                AttachAction::make()->preloadRecordSelect()->label('Add / Attach')->color('primary'),
            ])
            ->recordActions([
                ViewAction::make()->resource(RoleResource::class),
                DetachAction::make()->label('Remove'),
            ]);
    }
}
