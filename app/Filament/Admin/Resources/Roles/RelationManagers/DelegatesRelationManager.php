<?php

namespace App\Filament\Admin\Resources\Roles\RelationManagers;

use App\Models\Mship\Account;
use App\Services\Roles\DelegateRoleManagementService;
use Closure;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DelegatesRelationManager extends RelationManager
{
    protected static string $relationship = 'users';

    protected static ?string $title = 'Individuals';

    protected static ?string $recordTitleAttribute = 'name';

    public function table(Table $table): Table
    {
        $service = new DelegateRoleManagementService;

        return $table
            ->query(Account::query())
            ->modifyQueryUsing(function (Builder $query, bool $isResolvingRecord) use ($service) {
                if ($isResolvingRecord) {
                    return $query;
                }

                $role = $this->getOwnerRecord();

                if (! $service->delegatePermissionExists($role)) {
                    return $query->whereRaw('1 = 0');
                }

                return $service->filterDelegates($query, $role);
            })
            ->columns([
                TextColumn::make('id')
                    ->label('CID')
                    ->searchable(),
                TextColumn::make('name')
                    ->label('Name')
                    ->searchable(),
            ])
            ->headerActions([
                Action::make('add_delegate')
                    ->label('Add Delegate')
                    ->icon('heroicon-o-user-plus')
                    ->color('success')
                    ->schema([
                        Select::make('users_id')
                            ->label('Member')
                            ->searchable()
                            ->getSearchResultsUsing(fn (string $search) => $service->getPotentialDelegates($search))
                            ->getOptionLabelUsing(fn ($value): ?string => Account::find($value)?->name)
                            // The search above is a convenience only; never trust the
                            // submitted id to be an existing, eligible staff member.
                            ->rules([
                                fn (): Closure => function (string $attribute, mixed $value, Closure $fail) {
                                    $account = Account::find($value);

                                    if ($account === null || ! $account->can('admin.access')) {
                                        $fail('The selected member is not eligible to be delegated this role.');
                                    }
                                },
                            ])
                            ->required(),
                    ])
                    ->modalHeading(fn () => "Delegate '{$this->getOwnerRecord()->name}'")
                    ->modalDescription('Select a member to grant permission to manage this role.')
                    ->modalSubmitActionLabel('Delegate')
                    ->action(function (array $data) use ($service) {
                        $role = $this->getOwnerRecord();

                        $account = Account::where('id', $data['users_id'])->first();
                        $service->grantDelegate($account, $role);

                        Notification::make()
                            ->title('Delegation successful')
                            ->body("{$account->name} can now manage {$role->name}.")
                            ->success()
                            ->send();
                    })
                    ->visible(fn () => $service->delegatePermissionExists($this->getOwnerRecord()) && Auth()->user()->can('role.manage-delegates.*')),

                Action::make('remove_delegate_permission')
                    ->label('Remove Delegate Permission')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Remove Delegate Permission')
                    ->modalDescription('Deletes the role-specific permission allowing management of this role.')
                    ->modalSubmitActionLabel('Delete Permission')
                    ->action(function () use ($service) {
                        $role = $this->getOwnerRecord();
                        $service->deleteDelegatePermission($role);

                        Notification::make()
                            ->title('Permission deleted')
                            ->body("Delegate permission for {$role->name} removed.")
                            ->success()
                            ->send();
                    })
                    ->visible(fn () => $service->delegatePermissionExists($this->getOwnerRecord()) && Auth()->user()->can('role.manage-delegates.*')),
            ])
            ->recordActions([
                Action::make('remove_delegate')
                    ->label('Remove')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading(fn (Account $record) => "Remove {$record->name}?")
                    ->modalDescription(fn () => "This will revoke their permission to manage {$this->getOwnerRecord()->name}.")
                    ->action(function (Account $record) use ($service) {
                        $role = $this->getOwnerRecord();
                        $service->revokeDelegate($record, $role);

                        Notification::make()
                            ->title('Delegate Removed')
                            ->body("{$record->name} no longer has permissions to manage {$role->name}.")
                            ->success()
                            ->send();
                    })
                    ->after(fn () => $this->dispatch('$refresh'))
                    ->visible(fn () => Auth()->user()->can('role.manage-delegates.*')),
            ])
            ->emptyStateHeading('No Delegates')
            ->emptyStateDescription(function () use ($service) {
                $role = $this->getOwnerRecord();
                if (! $service->delegatePermissionExists($role)) {
                    return 'Create a delegate permission first to enable delegation.';
                }

                return 'No users have been delegated permission to manage this role.';
            })
            ->emptyStateIcon('heroicon-o-user-group')
            ->emptyStateActions([
                Action::make('create_permission')
                    ->label('Create Delegate Permission')
                    ->icon('heroicon-o-key')
                    ->color('info')
                    ->action(function () use ($service) {
                        $role = $this->getOwnerRecord();
                        $service->createDelegatePermission($role);

                        Notification::make()
                            ->title('Permission created')
                            ->body("Delegate permission for {$role->name} created.")
                            ->success()
                            ->send();
                    })
                    ->visible(fn () => ! $service->delegatePermissionExists($this->getOwnerRecord()) && Auth()->user()->can('role.manage-delegates.*')),
            ]);
    }
}
