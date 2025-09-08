<?php

namespace App\Filament\Admin\Resources\RoleResource\RelationManagers;

use App\Filament\Admin\Resources\AccountResource;
use Filament\Actions\AttachAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Actions\ViewAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersRelationManager extends RelationManager
{
    protected static string $relationship = 'users';

    protected static ?string $recordTitleAttribute = 'id';

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable(),
            ])
            ->headerActions([
                AttachAction::make()
                    ->recordSelectSearchColumns(AccountResource::getGloballySearchableAttributes())
                    ->recordTitle(fn ($record) => "$record->name ($record->id)"),
            ])
            ->recordActions([
                ViewAction::make()->resource(AccountResource::class),
                DetachAction::make()
                    ->using(function ($record, $livewire) {
                        $record->removeRole($livewire->ownerRecord);
                    }),
            ])
            ->toolbarActions([
                DetachBulkAction::make()
                    ->using(function ($records, $livewire) {
                        $records->forEach(fn ($record) => $record->removeRole($livewire->ownerRecord));
                    }),
            ]);
    }
}
