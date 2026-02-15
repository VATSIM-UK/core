<?php

namespace App\Filament\Admin\Resources\RoleResource\Pages;

use Spatie\Permission\Models\Role;
use App\Filament\Admin\Helpers\Pages\BaseEditRecordPage;
use App\Filament\Admin\Resources\RoleResource;
use Filament\Actions;
use Filament\Actions\ActionGroup;
use App\Models\Permission;
use Filament\Notifications\Notification;
use App\Filament\Admin\Forms\Components\AccountSelect;
use App\Models\Mship\Account;
use Filament\Forms\Components\Select;

class EditRole extends BaseEditRecordPage
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
            ActionGroup::make([
                Actions\Action::make('make_delegate_permission')
                    ->label('Add Delegate Permission')
                    ->icon('heroicon-o-key')
                    ->color('info')
                    ->requiresConfirmation()
                    ->modalHeading('Create Delegate Permission')
                    ->modalDescription('Creates a role-specific permission allowing delegation.')
                    ->modalSubmitActionLabel('Create Permission')
                    ->action(function (Role $record) {
                        $this->createDelegatePermission($record);

                        Notification::make()
                            ->title('Permission created')
                            ->body("Delegate permission for {$record->name} created.")
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Role $record) => ! $this->delegatePermissionExists($record)),

                Actions\Action::make('assign_delegation_to_user')
                    ->label('Delegate Role')
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
                    ->modalHeading(fn (Role $record) => "Delegate '{$record->name}'")
                    ->modalDescription('Select a member to grant role management permission.')
                    ->modalSubmitActionLabel('Delegate')
                    ->action(function (Role $record, array $data) {
                        $account = Account::where('id', $data['account_id'])->firstOrFail();

                        if ($account->can($this->delegatePermissionName($record)))
                        {
                            Notification::make()
                                ->title('Delegation unsuccessful')
                                ->body("{$account->name} can already manage {$record->name}.")
                                ->warning()
                                ->send();

                                return;
                        }
                        $account->givePermissionTo($this->delegatePermissionName($record));

                        Notification::make()
                            ->title('Delegation successful')
                            ->body("{$account->name} can now manage {$record->name}.")
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Role $record) => $this->delegatePermissionExists($record)),

                Actions\Action::make('remove_delegate_permission')
                    ->label('Remove Delegate Permission')
                    ->icon('heroicon-o-trash')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Remove Delegate Permission')
                    ->modalDescription('Deletes the delegation permission for this role.')
                    ->modalSubmitActionLabel('Delete Permission')
                    ->action(function (Role $record) {
                        $this->deleteDelegatePermission($record);

                        Notification::make()
                            ->title('Permission deleted')
                            ->body("Delegate permission for {$record->name} removed.")
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Role $record) => $this->delegatePermissionExists($record)),
            ]),
        ];
    }

    protected function delegatePermissionName(Role $role): string
    {
        return "account.edit-roles.{$role->id}";
    }

    protected function delegatePermissionExists(Role $role): bool
    {
        return Permission::where('name', $this->delegatePermissionName($role))->exists();
    }

    protected function createDelegatePermission(Role $role): void
    {
        Permission::create(['name' => $this->delegatePermissionName($role), 'guard_name' => 'web']);
    }

    protected function deleteDelegatePermission(Role $role): void
    {
        Permission::where('name', $this->delegatePermissionName($role))->first()->delete();
    }
}
