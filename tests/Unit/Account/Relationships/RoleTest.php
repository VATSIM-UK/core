<?php

namespace Tests\Unit\Account\Relationships;

use App\Events\Mship\Roles\RoleAssigned;
use App\Events\Mship\Roles\RoleRemoved;
use App\Models\Mship\Account;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class RoleTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function itStoresRoleDetails()
    {
        $role = factory(Role::class)->create();

        $this->assertDatabaseHas('mship_role', [
            'id' => $role->id,
        ]);
    }

    /** @test */
    public function itCorrectlyDeterminesIfThePasswordIsMandatory()
    {
        $role = factory(Role::class)->create(['password_mandatory' => true]);

        $this->assertTrue($role->password_mandatory);
    }

    /** @test */
    public function itCorrectlyDeterminesIfAPasswordLifetimeExists()
    {
        $role = factory(Role::class)->create(['password_lifetime' => 30]);

        $this->assertEquals($role->password_lifetime, 30);
    }

    /** @test */
    public function itCorrectlyDeterminesThatTheRoleHasASessionTimeout()
    {
        $role = factory(Role::class)->create(['session_timeout' => 60]);

        $this->assertEquals($role->session_timeout, 60);
    }

    /** @test */
    public function itCorrectlyDeterminesThatThisRoleHasASpecificPermission()
    {
        $role = factory(Role::class)->create();
        $permission = factory(Permission::class)->create(['name' => 'adm/visit-transfer/dashboard']);

        $role->givePermissionTo($permission);

        $this->assertDatabaseHas('mship_role_permission', [
            'permission_id' => $permission->id,
            'role_id' => $role->id,
        ]);

        $this->assertTrue($role->fresh()->hasPermissionTo($permission));
    }

    /** @test */
    public function itCorrectlyDeterminesThatThisRoleDoesNotHaveASpecificPermission()
    {
        $role = factory(Role::class)->create();
        $permissionA = factory(Permission::class)->create(['name' => 'adm/visit-transfer/dashboard']);
        $permissionB = factory(Permission::class)->create(['name' => 'adm/visit-transfer/elsewhere']);

        $role->givePermissionTo($permissionA);

        $this->assertDatabaseHas('mship_role_permission', [
            'role_id' => $role->id,
            'permission_id' => $permissionA->id,
        ]);

        $this->assertDatabaseMissing('mship_role_permission', [
            'role_id' => $role->id,
            'permission_id' => $permissionB->id,
        ]);

        $this->assertTrue($role->fresh()->hasPermissionTo($permissionA));
        $this->assertFalse($role->fresh()->hasPermissionTo($permissionB));
    }

    /** @test */
    public function itFiresEventsWhenARoleIsAssignedAndRemoved()
    {
        Event::fake();

        $role = factory(Role::class)->create();
        $account = Account::factory()->create();

        $account->assignRole($role);

        Event::assertDispatched(RoleAssigned::class);

        $account->removeRole($role);

        Event::assertDispatched(RoleRemoved::class);
    }
}
