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
use Illuminate\Support\Facades\Auth;
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
                    ->modalDescription('Select a member to grant permission to manage this role.')
                    ->modalSubmitActionLabel('Delegate')
                    ->action(function (array $data) use ($service) {
                        $role = $this->getOwnerRecord();

                        $account = Account::where('id', $data['account_id'])->first();
                        $account->givePermissionTo($service->delegatePermissionName($role));

                        Notification::make()
                            ->title('Delegation successful')
                            ->body("{$account->name} can now manage {$role->name}.")
                            ->success()
                            ->send();
                    })
                    ->visible(fn () => $service->delegatePermissionExists($this->getOwnerRecord()) && Auth()->user()->can('role.manage-delegates.*')),
                
                Tables\Actions\Action::make('remove_delegate_permission')
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
                    ->visible(fn () => !$service->delegatePermissionExists($this->getOwnerRecord()) && Auth()->user()->can('role.manage-delegates.*')),
            ]);
    }
}