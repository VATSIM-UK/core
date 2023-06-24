<?php

namespace Tests\Unit\Account\Filament;

use App\Models\Mship\Account;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class FilamentAccessTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function itCanAccessFilamentWithRoleThatHasPermission()
    {
        $account = factory(Account::class)->create();

        $role = factory(Role::class)->create();
        $role->givePermissionTo('admin/beta');

        $account->assignRole($role);

        $this->assertTrue($account->canAccessFilament());
    }

    /** @test */
    public function itCantAccessFilamentWithRoleThatDoesNotHasPermission()
    {
        $account = factory(Account::class)->create();

        $permission = factory(Permission::class)->create(['name' => 'not/admin/beta']);
        $role = factory(Role::class)->create();

        $role->givePermissionTo($permission);

        $account->assignRole($role);

        $this->assertFalse($account->canAccessFilament());
    }
}
