<?php

namespace App\Filament\Admin\Resources\AccountResource\RelationManagers;

use App\Filament\Admin\Resources\RoleResource;
use App\Services\Roles\DelegateRoleManagementService;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Spatie\Permission\Models\Role;

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
                Tables\Actions\AttachAction::make()->preloadRecordSelect()->label('Add / Attach')->color('primary')
                    ->recordSelectOptionsQuery(function (Builder $query) {
                        $service = new DelegateRoleManagementService;

                        return $service->getManageableRolesQuery($query, auth()->user());
                    })
                    ->before(function (Tables\Actions\AttachAction $action, array $data) {
                        $service = new DelegateRoleManagementService;
                        $role = Role::find($data['recordId']);

                        if (! auth()->user()->can($service->delegatePermissionName($role))) {
                            Notification::make()
                                ->danger()
                                ->title('Permission denied')
                                ->body('You do not have permission to assign this role.')
                                ->send();

                            $action->cancel();
                        }
                    }),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->resource(RoleResource::class),
                Tables\Actions\DetachAction::make()->label('Remove'),
            ])->bulkActions([
                BulkAction::make('detach')
                    ->requiresConfirmation()
                    ->deselectRecordsAfterCompletion()
                    ->label('Detach Selected')
                    ->authorize(fn () => auth()->user()->can('account.edit-roles.*'))
                    ->action(function (Collection $records) {
                        $account = $this->getOwnerRecord();

                        foreach ($records as $role) {
                            $account->removeRole($role);
                        }

                        Notification::make()
                            ->title('Roles detached')
                            ->success()
                            ->send();
                    }),
            ])
            ->description('Only roles you have been delegated permission to manage are shown. There may be additional roles assigned to this account that are not visible to you.')
            ->modifyQueryUsing(function (Builder $query) {
                $service = new DelegateRoleManagementService;

                return $service->getManageableRolesQuery($query, auth()->user());
            });
    }
}
