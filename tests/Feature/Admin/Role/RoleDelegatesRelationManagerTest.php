<?php

namespace Tests\Feature\Admin\Role\RelationManagers;

use App\Filament\Admin\Resources\Roles\Pages\EditRole;
use App\Filament\Admin\Resources\Roles\RelationManagers\RoleDelegatesRelationManager;
use App\Models\Mship\Account;
use App\Services\Roles\DelegateRoleManagementService;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\Feature\Admin\BaseAdminTestCase;

class RoleDelegatesRelationManagerTest extends BaseAdminTestCase
{
    private Role $role;

    private Role $delegateRole;

    private DelegateRoleManagementService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $memberRole = Role::findByName('member');
        $memberRole->permissions()->detach();

        $this->user->givePermissionTo('account.view-insensitive.*');

        $this->service = new DelegateRoleManagementService;
        $this->role = Role::create(['name' => 'Managed Role', 'guard_name' => 'web']);
        $this->delegateRole = Role::create(['name' => 'Delegate Role', 'guard_name' => 'web']);
    }

    public function test_role_members_inherit_delegate_permission()
    {
        $this->service->createDelegatePermission($this->role);
        $this->service->grantDelegate($this->delegateRole, $this->role);

        $delegate = Account::factory()->createQuietly();
        $delegate->assignRole($this->delegateRole);

        $this->assertTrue($delegate->can($this->service->delegatePermissionName($this->role)));
    }

    public function test_can_add_and_list_role_delegate()
    {
        $this->user->givePermissionTo('role.view.*');
        $this->user->givePermissionTo('role.manage-delegates.*');
        $this->service->createDelegatePermission($this->role);

        Livewire::actingAs($this->user)
            ->test(RoleDelegatesRelationManager::class, [
                'ownerRecord' => $this->role,
                'pageClass' => EditRole::class,
            ])
            ->callTableAction('add_delegate', data: ['role_id' => $this->delegateRole->id])
            ->assertHasNoTableActionErrors()
            ->assertCanSeeTableRecords([$this->delegateRole]);

        $this->assertTrue(
            $this->delegateRole->fresh()->hasPermissionTo($this->service->delegatePermissionName($this->role))
        );
    }

    public function test_can_remove_role_delegate()
    {
        $this->user->givePermissionTo('role.view.*');
        $this->user->givePermissionTo('role.manage-delegates.*');
        $this->service->createDelegatePermission($this->role);
        $this->service->grantDelegate($this->delegateRole, $this->role);

        Livewire::actingAs($this->user)
            ->test(RoleDelegatesRelationManager::class, [
                'ownerRecord' => $this->role,
                'pageClass' => EditRole::class,
            ])
            ->callTableAction('remove_delegate', data: ['role_id' => $this->delegateRole->id])
            ->assertHasNoTableActionErrors();

        Livewire::actingAs($this->user)
            ->test(RoleDelegatesRelationManager::class, [
                'ownerRecord' => $this->role,
                'pageClass' => EditRole::class,
            ])
            ->assertCanNotSeeTableRecords([$this->delegateRole]);

        $this->assertFalse(
            $this->delegateRole->fresh()->hasPermissionTo($this->service->delegatePermissionName($this->role))
        );
    }
}
