<?php

namespace App\Filament\Resources\RoleResource\RelationManagers;

use App\Filament\Resources\AccountResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Table;
use Filament\Tables;

class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'users';

    protected static ?string $recordTitleAttribute = 'id';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->searchable(),
            ])
            ->headerActions([
                Tables\Actions\AttachAction::make()
                    ->recordSelectSearchColumns(AccountResource::getGloballySearchableAttributes())
                    ->recordTitle(fn ($record) => "$record->name ($record->id)"),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->resource(AccountResource::class),
                Tables\Actions\DetachAction::make()
                    ->using(function ($record, $livewire) {
                        $record->removeRole($livewire->ownerRecord);
                    }),
            ])
            ->bulkActions([
                Tables\Actions\DetachBulkAction::make()
                    ->using(function ($records, $livewire) {
                        $records->forEach(fn ($record) => $record->removeRole($livewire->ownerRecord));
                    }),
            ]);
    }
}
