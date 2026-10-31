<?php

namespace App\Filament\Admin\Resources\Roles\RelationManagers;

use App\Services\Roles\DelegateRoleManagementService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Role;

class RoleDelegatesRelationManager extends RelationManager
{
    protected static string $relationship = 'users';

    protected static ?string $title = 'Roles';

    protected static ?string $recordTitleAttribute = 'name';

    public function table(Table $table): Table
    {
        $service = new DelegateRoleManagementService;
        $role = $this->getOwnerRecord();

        return $table
            ->query(Role::query())
            ->modifyQueryUsing(function (Builder $query, bool $isResolvingRecord) use ($role, $service) {
                if ($isResolvingRecord) {
                    return $query;
                }

                if (! $service->delegatePermissionExists($role)) {
                    return $query->whereRaw('1 = 0');
                }

                return $service->filterRoleDelegates($query, $role);
            })
            ->columns([
                TextColumn::make('name')
                    ->label('Role')
                    ->searchable(),
            ])
            ->headerActions([
                Action::make('add_delegate')
                    ->label('Add Delegate')
                    ->icon('heroicon-o-user-group')
                    ->color('success')
                    ->schema([
                        Select::make('role_id')
                            ->label('Role')
                            ->searchable()
                            ->getSearchResultsUsing(
                                fn (string $search) => $service->getPotentialDelegateRoles($search, $this->getOwnerRecord())
                            )
                            ->getOptionLabelUsing(fn ($value): ?string => Role::find($value)?->name)
                            ->required(),
                    ])
                    ->modalHeading(fn () => "Delegate '{$this->getOwnerRecord()->name}' to a role")
                    ->modalDescription('Members of the selected role will be able to manage this role.')
                    ->modalSubmitActionLabel('Delegate')
                    ->action(function (array $data) use ($service) {
                        $role = $this->getOwnerRecord();
                        $delegateRole = Role::findOrFail($data['role_id']);

                        $service->grantDelegate($delegateRole, $role);

                        Notification::make()
                            ->title('Delegation successful')
                            ->body("Members of {$delegateRole->name} can now manage {$role->name}.")
                            ->success()
                            ->send();
                    })
                    ->visible(fn () => $service->delegatePermissionExists($this->getOwnerRecord()) && auth()->user()->can('role.manage-delegates.*')),
                Action::make('remove_delegate')
                    ->label('Remove Delegate')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->schema([
                        Select::make('role_id')
                            ->label('Role')
                            ->options(fn () => $service->getRoleDelegates($this->getOwnerRecord())->orderBy('name')->pluck('name', 'id'))
                            ->searchable()
                            ->required(),
                    ])
                    ->modalHeading('Remove Role Delegate')
                    ->modalDescription(fn () => "Members of the selected role will no longer inherit permission to manage {$this->getOwnerRecord()->name}.")
                    ->action(function (array $data) use ($service) {
                        $role = $this->getOwnerRecord();
                        $delegateRole = Role::findOrFail($data['role_id']);

                        $service->revokeDelegate($delegateRole, $role);

                        Notification::make()
                            ->title('Delegate Removed')
                            ->body("Members of {$delegateRole->name} no longer inherit permission to manage {$role->name}.")
                            ->success()
                            ->send();
                    })
                    ->visible(fn () => $service->getRoleDelegates($this->getOwnerRecord())->exists() && auth()->user()->can('role.manage-delegates.*')),
            ])
            ->emptyStateHeading('No Role Delegates')
            ->emptyStateDescription(function () use ($service) {
                if (! $service->delegatePermissionExists($this->getOwnerRecord())) {
                    return 'Create a delegate permission from the Individuals tab first.';
                }

                return 'No roles have been delegated permission to manage this role.';
            })
            ->emptyStateIcon('heroicon-o-user-group');
    }
}
