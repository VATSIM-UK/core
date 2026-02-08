<?php

namespace App\Filament\Admin\Resources\AccountResource\RelationManagers;

use Filament\Forms;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Mship\Role;

class DelegatedRolesRelationManager extends RelationManager
{
    protected static string $relationship = 'delegatedRoles';
    protected static ?string $title = 'Delegated Roles';
    protected static ?string $inverseRelationship = 'users';
    protected static ?string $recordTitleAttribute = 'name';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Role Name')->sortable()->searchable(),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->authorize(fn () => auth()->user()->can('role.delegate.*'))
                    ->color('primary')
                    ->label('Delegate')
                    ->modalHeading('Delegate administration of role')
                    ->modalSubheading('Delagating a role will allow this user to assign and remove that role from all members')
                    ->preloadRecordSelect()
                    ->modalSubmitActionLabel('Delegate')
                    ->attachAnother(false)  
            ])
            ->actions([
                Tables\Actions\DetachAction::make()->label('Undelegate'),
            ])
            ->bulkActions([
                Tables\Actions\DetachBulkAction::make(),
            ]);
    }

    public function isReadOnly(): bool
    {
        return false;
    }
}
