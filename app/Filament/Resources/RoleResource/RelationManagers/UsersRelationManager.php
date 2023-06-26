<?php

namespace App\Filament\Resources\RoleResource\RelationManagers;

use App\Filament\Helpers\ResourceManagers\MakesExternalViewButtons;
use App\Filament\Resources\AccountResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Table;
use Filament\Tables;

class UsersRelationManager extends RelationManager
{
    use MakesExternalViewButtons;

    protected static string $relationship = 'users';

    protected static ?string $recordTitleAttribute = 'id';

    public static function table(Table $table): Table
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
                self::resourceViewAction(AccountResource::class),
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
