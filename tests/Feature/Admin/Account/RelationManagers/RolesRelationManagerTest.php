<?php

namespace Tests\Feature\Admin\Account\RelationManagers;

use App\Filament\Admin\Resources\Accounts\Pages\ViewAccount;
use App\Filament\Admin\Resources\Accounts\RelationManagers\RolesRelationManager;
use App\Models\Mship\Account;
use App\Services\Roles\DelegateRoleManagementService;
use Livewire\Livewire;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\Feature\Admin\BaseAdminTestCase;

class RolesRelationManagerTest extends BaseAdminTestCase
{
    private DelegateRoleManagementService $service;

    private Account $targetAccount;

    private Role $role1;

    private Role $role2;

    private Role $role3;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new DelegateRoleManagementService;
        $this->targetAccount = Account::factory()->createQuietly();

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

    public function test_role_delegate_can_only_see_delegated_roles()
    {
        $this->user->givePermissionTo('account.view-insensitive.*');

        $delegateRole = Role::create(['name' => 'Role Managers', 'guard_name' => 'web']);
        $this->user->assignRole($delegateRole);

        $this->service->createDelegatePermission($this->role1);
        $this->service->createDelegatePermission($this->role2);
        $this->service->grantDelegate($delegateRole, $this->role1);

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

    public function test_wildcard_user_can_attach_any_role()
    {
        $this->user->givePermissionTo('account.view-insensitive.*');
        $this->user->givePermissionTo('account.edit-roles.*');

        $this->service->createDelegatePermission($this->role1);
        $this->service->createDelegatePermission($this->role2);

        Livewire::actingAs($this->user)
            ->test(RolesRelationManager::class, [
                'ownerRecord' => $this->targetAccount,
                'pageClass' => ViewAccount::class,
            ])
            ->callTableAction('attach', data: ['recordId' => $this->role1->id])
            ->assertHasNoTableActionErrors();

        $this->assertTrue($this->user->can($this->service->delegatePermissionName($this->role1)));
    }

    public function test_delegate_user_can_only_attach_permitted_roles()
    {
        $this->user->givePermissionTo('account.view-insensitive.*');

        $this->service->createDelegatePermission($this->role1);
        $this->service->createDelegatePermission($this->role2);

        $this->user->givePermissionTo($this->service->delegatePermissionName($this->role1));

        Livewire::actingAs($this->user)
            ->test(RolesRelationManager::class, [
                'ownerRecord' => $this->targetAccount,
                'pageClass' => ViewAccount::class,
            ])
            ->callTableAction('attach', data: ['recordId' => $this->role1->id])
            ->assertHasNoTableActionErrors();

        $this->assertTrue($this->user->can($this->service->delegatePermissionName($this->role1)));

        Livewire::actingAs($this->user)
            ->test(RolesRelationManager::class, [
                'ownerRecord' => $this->targetAccount,
                'pageClass' => ViewAccount::class,
            ])
            ->callTableAction('attach', data: ['recordId' => $this->role2->id]);

        $this->assertFalse($this->user->can($this->service->delegatePermissionName($this->role2)));
    }

    public function test_wildcard_delegate_cannot_attach_a_role_above_their_privilege()
    {
        $this->user->givePermissionTo(['account.view-insensitive.*', 'account.edit-roles.*']);

        $privaccRole = Role::findByName('privacc');

        Livewire::actingAs($this->user)
            ->test(RolesRelationManager::class, [
                'ownerRecord' => $this->targetAccount,
                'pageClass' => ViewAccount::class,
            ])
            ->callTableAction('attach', data: ['recordId' => $privaccRole->id]);

        $this->assertFalse($this->targetAccount->fresh()->hasRole($privaccRole));
    }

    public function test_delegate_cannot_attach_a_role_that_grants_permissions_they_do_not_hold()
    {
        $privilegedRole = Role::create(['name' => 'Privileged Delegated Role', 'guard_name' => 'web']);
        $privilegedRole->givePermissionTo(Permission::findByName('account.view-sensitive.*'));

        $this->service->createDelegatePermission($privilegedRole);

        $this->user->givePermissionTo([
            'account.view-insensitive.*',
            $this->service->delegatePermissionName($privilegedRole),
        ]);

        Livewire::actingAs($this->user)
            ->test(RolesRelationManager::class, [
                'ownerRecord' => $this->targetAccount,
                'pageClass' => ViewAccount::class,
            ])
            ->callTableAction('attach', data: ['recordId' => $privilegedRole->id]);

        $this->assertFalse($this->targetAccount->fresh()->hasRole($privilegedRole));
    }

    public function test_super_admin_can_attach_a_role_above_a_delegates_privilege()
    {
        $privaccRole = Role::findByName('privacc');

        Livewire::actingAs($this->privacc)
            ->test(RolesRelationManager::class, [
                'ownerRecord' => $this->targetAccount,
                'pageClass' => ViewAccount::class,
            ])
            ->callTableAction('attach', data: ['recordId' => $privaccRole->id])
            ->assertHasNoTableActionErrors();

        $this->assertTrue($this->targetAccount->fresh()->hasRole($privaccRole));
    }
}
