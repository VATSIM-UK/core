<?php

namespace App\Filament\Admin\Resources\RoleResource\RelationManagers;

use App\Models\Mship\Account;
use App\Services\Roles\DelegateRoleManagementService;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Models\Role;

class DelegatesRelationManager extends RelationManager
{
    protected static string $relationship = 'users';
    
    protected static ?string $title = 'Delegates';
    
    protected static ?string $recordTitleAttribute = 'name';

    public function table(Table $table): Table
    {
        $service = new DelegateRoleManagementService();
        
        return $table
            ->modifyQueryUsing(function (Builder $query) use ($service) {
                $role = $this->getOwnerRecord();
                
                if (!$service->delegatePermissionExists($role)) {
                    return $query->whereRaw('1 = 0');
                }
                
                return $service->getDelegates($role);
            })
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('CID')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->label('Name')
                    ->searchable(),
            ])
            ->headerActions([
                Tables\Actions\Action::make('create_delegate_permission')
                    ->label('Create Delegate Permission')
                    ->icon('heroicon-o-key')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Create Delegate Permission')
                    ->modalDescription('Creates a role-specific permission allowing delegation.')
                    ->modalSubmitActionLabel('Create Permission')
                    ->action(function () use ($service) {
                        $role = $this->getOwnerRecord();
                        $service->createDelegatePermission($role);

                        Notification::make()
                            ->title('Permission created')
                            ->body("Delegate permission for {$role->name} created.")
                            ->success()
                            ->send();
                    })
                    ->visible(fn () => !$service->delegatePermissionExists($this->getOwnerRecord())),

                Tables\Actions\Action::make('remove_delegate_permission')
                    ->label('Remove Delegate Permission')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Remove Delegate Permission')
                    ->modalDescription('Deletes the role-specific permission allowing delegation.')
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
                    ->visible(fn () => $service->delegatePermissionExists($this->getOwnerRecord())),
                    
                Tables\Actions\Action::make('add_delegate')
                    ->label('Add Delegate')
                    ->icon('heroicon-o-user-plus')
                    ->color('success')
                    ->form([
                        Select::make('account_id')
                            ->label('Account')
                            ->required()
                            ->searchable()
                            ->getSearchResultsUsing(function (string $search) {
                                $accounts = Account::query()
                                    ->where('id', 'like', "%{$search}%")
                                    ->orWhere('name_first', 'like', "%{$search}%")
                                    ->orWhere('name_last', 'like', "%{$search}%")
                                    ->limit(50)
                                    ->get();

                                return $accounts->mapWithKeys(fn ($account) => [
                                    $account->id => $account->name_first . ' ' . $account->name_last . ' - ' . $account->id
                                ])->toArray();
                            })
                            ->getOptionLabelFromRecordUsing(fn($record) => $record),
                    ])
                    ->modalHeading(fn () => "Delegate '{$this->getOwnerRecord()->name}'")
                    ->modalDescription('Select a member to grant role management permission.')
                    ->modalSubmitActionLabel('Delegate')
                    ->action(function (array $data) use ($service) {
                        $role = $this->getOwnerRecord();
                        $account = Account::where('id', $data['account_id'])->firstOrFail();

                        if ($account->can($service->delegatePermissionName($role))) {
                            Notification::make()
                                ->title('Delegation unsuccessful')
                                ->body("{$account->name} can already manage {$role->name}.")
                                ->warning()
                                ->send();
                            return;
                        }
                        
                        $account->givePermissionTo($service->delegatePermissionName($role));

                        Notification::make()
                            ->title('Delegation successful')
                            ->body("{$account->name} can now manage {$role->name}.")
                            ->success()
                            ->send();
                    })
                    ->visible(fn () => $service->delegatePermissionExists($this->getOwnerRecord())),
            ])
            ->actions([
                Tables\Actions\DetachAction::make()
                    ->label('Remove')
                    ->modalHeading('Remove Delegate')
                    ->modalDescription(fn (Account $record) => "Remove {$record->name}'s permission to manage this role?")
                    ->action(function (Account $record) use ($service) {
                        $role = $this->getOwnerRecord();
                        $service->revokeDelegate($record, $role);

                        Notification::make()
                            ->title('Delegate Removed')
                            ->body("{$record->name} no longer has permissions to manage {$role->name}.")
                            ->success()
                            ->send();
                    }),
            ])
            ->emptyStateHeading('No Delegates')
            ->emptyStateDescription(function () use ($service) {
                $role = $this->getOwnerRecord();
                if (!$service->delegatePermissionExists($role)) {
                    return 'Create a delegate permission first to enable delegation.';
                }
                return 'No users have been delegated permission to manage this role.';
            })
            ->emptyStateIcon('heroicon-o-user-group')
            ->emptyStateActions([
                Tables\Actions\Action::make('create_permission')
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
                    ->visible(fn () => !$service->delegatePermissionExists($this->getOwnerRecord())),
            ]);
    }
}