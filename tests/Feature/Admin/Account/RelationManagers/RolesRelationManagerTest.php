<?php

namespace Tests\Feature\Admin\Account\RelationManagers;

use App\Filament\Admin\Resources\AccountResource\Pages\ViewAccount;
use App\Filament\Admin\Resources\AccountResource\RelationManagers\RolesRelationManager;
use App\Models\Mship\Account;
use App\Services\Roles\DelegateRoleManagementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\Feature\Admin\BaseAdminTestCase;

class RolesRelationManagerTest extends BaseAdminTestCase
{
    use RefreshDatabase;

    private DelegateRoleManagementService $service;

    private Account $targetAccount;

    private Role $role1;

    private Role $role2;

    private Role $role3;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new DelegateRoleManagementService;
        $this->targetAccount = Account::factory()->create();

        $this->role1 = Role::create(['name' => 'Role 1', 'guard_name' => 'web']);
        $this->role2 = Role::create(['name' => 'Role 2', 'guard_name' => 'web']);
        $this->role3 = Role::create(['name' => 'Role 3', 'guard_name' => 'web']);
    }

    public function test_wildcard_user_can_see_all_roles()
    {
        $this->targetAccount->syncRoles([]);
        
        $this->user->givePermissionTo('account.view-insensitive.*');
        $this->user->givePermissionTo('account.edit-roles.*');

        $this->targetAccount->assignRole($this->role1);
        $this->targetAccount->assignRole($this->role2);

        Livewire::actingAs($this->user)
            ->test(RolesRelationManager::class, [
                'ownerRecord' => $this->targetAccount,
                'pageClass' => ViewAccount::class,
            ])
            ->assertCanSeeTableRecords([$this->role1, $this->role2])
            ->assertCountTableRecords(2);
    }

    public function test_delegate_user_can_only_see_delegated_roles()
    {
        $this->user->givePermissionTo('account.view-insensitive.*');

        $this->service->createDelegatePermission($this->role1);
        $this->service->createDelegatePermission($this->role2);

        $this->user->givePermissionTo($this->service->delegatePermissionName($this->role1));

        $this->targetAccount->assignRole($this->role1);
        $this->targetAccount->assignRole($this->role2);

        Livewire::actingAs($this->user)
            ->test(RolesRelationManager::class, [
                'ownerRecord' => $this->targetAccount,
                'pageClass' => ViewAccount::class,
            ])
            ->assertCanSeeTableRecords([$this->role1])
            ->assertCanNotSeeTableRecords([$this->role2])
            ->assertCountTableRecords(1);
    }
}
