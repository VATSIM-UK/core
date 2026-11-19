<?php

namespace Tests\Feature\Admin\Role;

use App\Filament\Admin\Resources\Roles\Pages\EditRole;
use App\Filament\Admin\Resources\Roles\Pages\ListRoles;
use App\Filament\Admin\Resources\Roles\RoleResource;
use App\Policies\RolePolicy;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\Feature\Admin\BaseAdminResourceTestCase;

class RoleResourceTest extends BaseAdminResourceTestCase
{
    protected static ?string $resourceClass = RoleResource::class;

    protected ?string $policy = RolePolicy::class;

    #[Test]
    public function it_hides_sync_discord_table_action_without_role_sync_discord_permission(): void
    {
        $role = Role::create(['name' => 'Role For Sync Discord Visibility', 'guard_name' => 'web']);

        $this->actingAsAdminUser(['role.view.*']);

        Livewire::test(ListRoles::class)
            ->assertSuccessful()
            ->assertTableActionHidden('syncDiscord', $role);
    }

    #[Test]
    public function it_sorts_roles_by_name_and_defaults_to_ascending(): void
    {
        $alpha = Role::create(['name' => 'Zzsortrole Alpha', 'guard_name' => 'web']);
        $zulu = Role::create(['name' => 'Zzsortrole Zulu', 'guard_name' => 'web']);

        $this->actingAsAdminUser(['role.view.*']);

        Livewire::test(ListRoles::class)
            ->searchTable('Zzsortrole')
            ->assertCanSeeTableRecords([$alpha, $zulu], inOrder: true)
            ->sortTable('name', 'desc')
            ->assertCanSeeTableRecords([$zulu, $alpha], inOrder: true);
    }

    #[Test]
    public function it_shows_sync_discord_table_action_with_role_sync_discord_permission(): void
    {
        $role = Role::create(['name' => 'Role For Sync Discord Visible', 'guard_name' => 'web']);

        $this->actingAsAdminUser(['role.view.*', 'role.sync-discord.*']);

        Livewire::test(ListRoles::class)
            ->assertSuccessful()
            ->assertTableActionVisible('syncDiscord', $role);
    }

    #[Test]
    public function it_does_not_allow_a_role_editor_to_grant_a_permission_they_do_not_hold(): void
    {
        $star = Permission::findByName('*');
        $grantable = Permission::findByName('account.view-insensitive.*');

        $role = Role::create(['name' => 'Role For Permission Escalation', 'guard_name' => 'web']);
        $role->givePermissionTo($grantable);

        $this->actingAsAdminUser(['role.view.*', 'role.edit.*', 'account.view-insensitive.*']);

        Livewire::test(EditRole::class, ['record' => $role->id])
            ->fillForm(['permissions' => [$grantable->id, $star->id]])
            ->call('save')
            ->assertHasFormErrors(['permissions']);

        $this->assertFalse($role->fresh()->hasPermissionTo('*'));
    }

    #[Test]
    public function it_preserves_permissions_the_role_editor_cannot_grant_when_saving_a_role(): void
    {
        $star = Permission::findByName('*');

        $role = Role::create(['name' => 'Role With Superadmin Permission', 'guard_name' => 'web']);
        $role->givePermissionTo($star);

        $this->actingAsAdminUser(['role.view.*', 'role.edit.*']);

        Livewire::test(EditRole::class, ['record' => $role->id])
            ->fillForm(['name' => 'Renamed Role Without Dropping Permissions'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue($role->fresh()->hasPermissionTo('*'));
    }

    #[Test]
    public function it_allows_a_super_admin_to_grant_any_permission_to_a_role(): void
    {
        $star = Permission::findByName('*');

        $role = Role::create(['name' => 'Role For Superadmin Permission Grant', 'guard_name' => 'web']);

        $this->actingAsSuperUser();

        Livewire::test(EditRole::class, ['record' => $role->id])
            ->fillForm(['permissions' => [$star->id]])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue($role->fresh()->hasPermissionTo('*'));
    }

    #[Test]
    public function it_does_not_allow_the_bulk_permission_action_to_grant_permissions_the_actor_does_not_hold(): void
    {
        $role = Role::create(['name' => 'Role For Bulk Permission Escalation', 'guard_name' => 'web']);

        $this->actingAsAdminUser(['role.view.*', 'role.edit.*']);

        Livewire::test(ListRoles::class)
            ->callTableBulkAction('assignPermissions', [$role], ['permissions' => ['*']])
            ->assertHasActionErrors(['permissions']);

        $this->assertFalse($role->fresh()->hasPermissionTo('*'));
    }

    #[Test]
    public function it_allows_a_super_admin_to_use_the_bulk_permission_action(): void
    {
        $role = Role::create(['name' => 'Role For Superadmin Bulk Permission Grant', 'guard_name' => 'web']);

        $this->actingAsSuperUser();

        Livewire::test(ListRoles::class)
            ->callTableBulkAction('assignPermissions', [$role], ['permissions' => ['*']])
            ->assertHasNoActionErrors();

        $this->assertTrue($role->fresh()->hasPermissionTo('*'));
    }
}
