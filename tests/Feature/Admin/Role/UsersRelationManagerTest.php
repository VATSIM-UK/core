<?php

namespace Tests\Feature\Admin\Role;

use App\Filament\Admin\Resources\Roles\Pages\EditRole;
use App\Filament\Admin\Resources\Roles\RelationManagers\UsersRelationManager;
use App\Models\Mship\Account;
use App\Services\Roles\DelegateRoleManagementService;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\Feature\Admin\BaseAdminTestCase;

class UsersRelationManagerTest extends BaseAdminTestCase
{
    private Role $role;

    protected function setUp(): void
    {
        parent::setUp();

        $this->role = Role::create(['name' => 'Delegated Role', 'guard_name' => 'web']);
    }

    #[Test]
    public function it_hides_the_attach_action_without_the_role_delegate_permission(): void
    {
        $this->actingAsAdminUser(['account.view-insensitive.*', 'role.view.*', 'role.edit.*']);

        Livewire::test(UsersRelationManager::class, [
            'ownerRecord' => $this->role,
            'pageClass' => EditRole::class,
        ])->assertTableActionHidden('attach');
    }

    #[Test]
    public function it_does_not_attach_a_user_without_the_role_delegate_permission(): void
    {
        $this->actingAsAdminUser(['account.view-insensitive.*', 'role.view.*', 'role.edit.*']);

        $member = Account::factory()->createQuietly();

        Livewire::test(UsersRelationManager::class, [
            'ownerRecord' => $this->role,
            'pageClass' => EditRole::class,
        ])
            ->mountTableAction('attach')
            ->assertTableActionNotMounted('attach');

        $this->assertFalse($member->fresh()->hasRole($this->role));
    }

    #[Test]
    public function it_hides_the_detach_actions_without_the_role_delegate_permission(): void
    {
        DB::table('mship_account_role')->insert([
            'model_type' => Account::class,
            'model_id' => $this->privacc->id,
            'role_id' => $this->role->id,
        ]);

        $this->actingAsAdminUser(['account.view-insensitive.*', 'role.view.*', 'role.edit.*']);

        Livewire::test(UsersRelationManager::class, [
            'ownerRecord' => $this->role,
            'pageClass' => EditRole::class,
        ])
            ->assertTableActionHidden('detach', $this->privacc)
            ->assertTableBulkActionHidden('detach')
            ->mountTableAction('detach', $this->privacc)
            ->assertTableActionNotMounted('detach');

        $this->assertTrue($this->privacc->fresh()->hasRole($this->role));
    }

    #[Test]
    public function it_allows_a_delegate_to_attach_and_detach_users(): void
    {
        $service = new DelegateRoleManagementService;
        $service->createDelegatePermission($this->role);

        $this->actingAsAdminUser([
            'account.view-insensitive.*',
            'role.view.*',
            $service->delegatePermissionName($this->role),
        ]);

        $member = Account::factory()->createQuietly();

        Livewire::test(UsersRelationManager::class, [
            'ownerRecord' => $this->role,
            'pageClass' => EditRole::class,
        ])
            ->callTableAction('attach', data: ['recordId' => $member->id])
            ->assertHasNoTableActionErrors();

        $this->assertTrue($member->fresh()->hasRole($this->role));

        Livewire::test(UsersRelationManager::class, [
            'ownerRecord' => $this->role,
            'pageClass' => EditRole::class,
        ])
            ->callTableAction('detach', $member)
            ->assertHasNoTableActionErrors();

        $this->assertFalse($member->fresh()->hasRole($this->role));
    }

    #[Test]
    public function it_allows_a_super_admin_to_attach_users(): void
    {
        $this->actingAsSuperUser();

        $member = Account::factory()->createQuietly();

        Livewire::test(UsersRelationManager::class, [
            'ownerRecord' => $this->role,
            'pageClass' => EditRole::class,
        ])
            ->callTableAction('attach', data: ['recordId' => $member->id])
            ->assertHasNoTableActionErrors();

        $this->assertTrue($member->fresh()->hasRole($this->role));
    }
}
