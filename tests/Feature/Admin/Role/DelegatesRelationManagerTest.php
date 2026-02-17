<?php

namespace Tests\Feature\Admin\Role\RelationManagers;

use App\Filament\Admin\Resources\RoleResource\Pages\EditRole;
use App\Filament\Admin\Resources\RoleResource\RelationManagers\DelegatesRelationManager;
use App\Models\Mship\Account;
use App\Services\Roles\DelegateRoleManagementService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Spatie\Permission\Models\Role;
use Tests\Feature\Admin\BaseAdminTestCase;

class DelegatesRelationManagerTest extends BaseAdminTestCase
{
    use RefreshDatabase;

    private Role $role;

    private DelegateRoleManagementService $service;

    private Account $delegateAccount;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new DelegateRoleManagementService;
        $this->role = Role::create(['name' => 'Test Role', 'guard_name' => 'web']);
        $this->delegateAccount = Account::factory()->create();
    }

    public function test_shows_empty_state_when_no_delegate_permission_exists()
    {
        $this->user->givePermissionTo('role.view.*');
        $this->user->givePermissionTo('role.manage-delegates.*');

        Livewire::actingAs($this->user)
            ->test(DelegatesRelationManager::class, [
                'ownerRecord' => $this->role,
                'pageClass' => EditRole::class,
            ])
            ->assertSee('No Delegates')
            ->assertSee('Create a delegate permission first to enable delegation.');
    }

    public function test_cant_create_delegate_permission_without_permission()
    {
        $this->user->givePermissionTo('role.view.*');

        Livewire::actingAs($this->user)
            ->test(DelegatesRelationManager::class, [
                'ownerRecord' => $this->role,
                'pageClass' => EditRole::class,
            ])
            ->assertDontSee('Create Delegate Permission');
    }

    public function test_can_create_delegate_permission_with_permission()
    {
        $this->user->givePermissionTo('role.view.*');
        $this->user->givePermissionTo('role.manage-delegates.*');

        $expectedPermissionName = $this->service->delegatePermissionName($this->role);

        Livewire::actingAs($this->user)
            ->test(DelegatesRelationManager::class, [
                'ownerRecord' => $this->role,
                'pageClass' => EditRole::class,
            ])
            ->assertSee('Create Delegate Permission')
            ->call('mountAction', 'create_permission')
            ->call('callMountedAction');

        $this->assertDatabaseHas('mship_permission', ['name' => $expectedPermissionName, 'guard_name' => 'web']);
    }

    public function test_create_permission_action_hidden_when_permission_already_exists()
    {
        $this->user->givePermissionTo('role.view.*');
        $this->user->givePermissionTo('role.manage-delegates.*');

        $this->service->createDelegatePermission($this->role);

        Livewire::actingAs($this->user)
            ->test(DelegatesRelationManager::class, [
                'ownerRecord' => $this->role,
                'pageClass' => EditRole::class,
            ])
            ->assertDontSee('Create Delegate Permission');
    }

    public function test_cant_add_delegate_without_permission()
    {
        $this->user->givePermissionTo('role.view.*');
        $this->service->createDelegatePermission($this->role);

        Livewire::actingAs($this->user)
            ->test(DelegatesRelationManager::class, [
                'ownerRecord' => $this->role,
                'pageClass' => EditRole::class,
            ])
            ->assertTableActionHidden('add_delegate');
    }

    public function test_can_add_delegate_with_permission()
    {
        $this->user->givePermissionTo('role.view.*');
        $this->user->givePermissionTo('role.manage-delegates.*');

        $this->service->createDelegatePermission($this->role);
        $expectedPermissionName = $this->service->delegatePermissionName($this->role);

        Livewire::actingAs($this->user)
            ->test(DelegatesRelationManager::class, [
                'ownerRecord' => $this->role,
                'pageClass' => EditRole::class,
            ])
            ->assertTableActionVisible('add_delegate')
            ->callTableAction('add_delegate', data: [
                'account_id' => $this->delegateAccount->id,
            ]);

        $this->assertTrue($this->delegateAccount->fresh()->hasPermissionTo($expectedPermissionName));
    }

    public function test_table_shows_delegated_users()
    {
        $this->user->givePermissionTo('role.view.*');
        $this->user->givePermissionTo('role.manage-delegates.*');

        $this->service->createDelegatePermission($this->role);
        $delegate1 = Account::factory()->create(['name_first' => 'John', 'name_last' => 'Doe']);
        $delegate2 = Account::factory()->create(['name_first' => 'Jane', 'name_last' => 'Smith']);

        $delegate1->givePermissionTo($this->service->delegatePermissionName($this->role));
        $delegate2->givePermissionTo($this->service->delegatePermissionName($this->role));

        Livewire::actingAs($this->user)
            ->test(DelegatesRelationManager::class, [
                'ownerRecord' => $this->role,
                'pageClass' => EditRole::class,
            ])
            ->assertCanSeeTableRecords([$delegate1, $delegate2])
            ->assertCountTableRecords(2);
    }

    public function test_removing_delegate_permission_revokes_it_from_all_users()
    {
        $this->user->givePermissionTo('role.view.*');
        $this->user->givePermissionTo('role.manage-delegates.*');

        $this->service->createDelegatePermission($this->role);
        $permissionName = $this->service->delegatePermissionName($this->role);

        $delegate1 = Account::factory()->create();
        $delegate2 = Account::factory()->create();

        $delegate1->givePermissionTo($permissionName);
        $delegate2->givePermissionTo($permissionName);

        $this->assertTrue($delegate1->hasPermissionTo($permissionName));
        $this->assertTrue($delegate2->hasPermissionTo($permissionName));

        Livewire::actingAs($this->user)
            ->test(DelegatesRelationManager::class, [
                'ownerRecord' => $this->role,
                'pageClass' => EditRole::class,
            ])
            ->call('mountAction', 'remove_delegate_permission')
            ->call('callMountedAction');

        $this->assertFalse($delegate1->fresh()->hasPermissionTo($permissionName));
        $this->assertFalse($delegate2->fresh()->hasPermissionTo($permissionName));
    }
}
