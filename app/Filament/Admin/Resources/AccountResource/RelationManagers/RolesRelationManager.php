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
            ->modifyQueryUsing(function (Builder $query) {
                $service = new DelegateRoleManagementService;
                $user = auth()->user();

                if ($user->can('account.edit-roles.*')) {
                    return $query;
                }

                $manageableRoleIds = Role::all()->filter(function ($role) use ($service, $user) {
                    return $service->delegatePermissionExists($role)
                        && $user->hasPermissionTo($service->delegatePermissionName($role));
                })->pluck('id');

                return $query->whereIn('id', $manageableRoleIds);
            })
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
                    ->label('Detach Selected')
                    ->authorize(fn () => auth()->user()->can('adm/mship/account/*/roles/*/detach'))
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
            ]);
    }
}
