<?php

namespace App\Filament\Admin\Resources\AccountResource\RelationManagers;

use App\Filament\Admin\Resources\RoleResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\BulkAction;
use Illuminate\Database\Eloquent\Collection;
use Filament\Notifications\Notification;


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
            ])->bulkActions([
                BulkAction::make('detach')
                    ->requiresConfirmation()
                    ->deselectRecordsAfterCompletion()
                    ->label("Detach Selected")
                    ->authorize(fn () => auth()->user()->can('adm/mship/account/*/roles/*/detach'))
                    ->action(function (Collection $records) {
                        $this->getRelationship()->detach($records->pluck('id'));

                        Notification::make()
                            ->title('Roles detached')
                            ->success()
                            ->send();
                    })
            ]);
    }
}
