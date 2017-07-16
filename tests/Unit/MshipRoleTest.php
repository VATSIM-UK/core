<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\BrowserKitTestCase;
use Tests\TestCase;

class MshipRoleTest extends TestCase
{
    use DatabaseTransactions;

    /** @test * */
    public function itStoresRoleDetails()
    {
        $role = factory(\App\Models\Mship\Role::class)->create();

        $this->assertDatabaseHas("mship_role", [
            "id" => $role->id,
        ]);
    }

    /** @test * */
    public function itCorrectlyDeterminesIfThePasswordIsMandatory()
    {
        $role = factory(\App\Models\Mship\Role::class)->create(["password_mandatory" => true]);

        $this->assertTrue($role->hasMandatoryPassword());
    }

    /** @test * */
    public function itCorrectlyDeterminesIfAPasswordLifetimeExists()
    {
        $role = factory(\App\Models\Mship\Role::class)->create(["password_lifetime" => 30]);

        $this->assertTrue($role->hasPasswordLifetime());
    }

    /** @test * */
    public function itCorrectlyDeterminesThatTheRoleHasASessionTimeout()
    {
        $role = factory(\App\Models\Mship\Role::class)->create(["session_timeout" => 60]);

        $this->assertTrue($role->hasSessionTimeout());

        $this->assertDatabaseHas("mship_role", [
            "id"              => $role->id,
            "session_timeout" => 60,
        ]);
    }

    /** @test * */
    public function itCorrectlyLoadsTheDefaultRole()
    {
        $role = factory(\App\Models\Mship\Role::class)->create(["default" => true]);

        $defaultRole = \App\Models\Mship\Role::findDefault();

        $this->assertEquals($role->id, $defaultRole->id);
    }

    /** @test * */
    public function itCorrectlyRemovesTheDefaultStatusFromTheOldDefaultRoleWhenCreatingANewDefaultRole()
    {
        $roleOriginalDefault = factory(\App\Models\Mship\Role::class)->create(["default" => true]);

        $roleNewDefault = factory(\App\Models\Mship\Role::class)->create();
        $roleNewDefault->default = 1;
        $roleNewDefault->save();

        $this->assertFalse($roleOriginalDefault->fresh()->is_default);
        $this->assertTrue($roleNewDefault->fresh()->is_default);
    }

    /** @test */
    public function itCorrectlyDeterminesThatThisRoleHasASpecificPermission()
    {
        $role = factory(\App\Models\Mship\Role::class)->create();
        $permission = factory(\App\Models\Mship\Permission::class)->create(["name" => "adm/visit-transfer/dashboard"]);

        $role->attachPermission($permission);

        $this->assertDatabaseHas("mship_permission_role", [
            "role_id"       => $role->id,
            "permission_id" => $permission->id,
        ]);

        $this->assertTrue($role->fresh()->hasPermission($permission));
        $this->assertTrue($role->fresh()->hasPermission($permission->name));
    }

    /** @test */
    public function itCorrectlyDeterminesThatThisRoleDoesNotHaveASpecificPermission()
    {
        $role = factory(\App\Models\Mship\Role::class)->create();
        $permissionA = factory(\App\Models\Mship\Permission::class)->create(["name" => "adm/visit-transfer/dashboard"]);
        $permissionB = factory(\App\Models\Mship\Permission::class)->create(["name" => "adm/visit-transfer/elsewhere"]);

        $role->attachPermission($permissionA);

        $this->assertDatabaseHas("mship_permission_role", [
            "role_id"       => $role->id,
            "permission_id" => $permissionA->id,
        ]);

        $this->assertDatabaseMissing("mship_permission_role", [
            "role_id"       => $role->id,
            "permission_id" => $permissionB->id,
        ]);

        $this->assertTrue($role->fresh()->hasPermission($permissionA));
        $this->assertTrue($role->fresh()->hasPermission($permissionA->name));
        $this->assertFalse($role->fresh()->hasPermission($permissionB));
        $this->assertFalse($role->fresh()->hasPermission($permissionB->name));
    }

    /** @test */
    public function itCorrectlyDeterminesThatThisRoleHasASpecificPermissionByAlphaOnlyName()
    {
        $role = factory(\App\Models\Mship\Role::class)->create();
        $permission = factory(\App\Models\Mship\Permission::class)->create(["name" => "adm/visit-transfer/dashboard"]);

        $role->attachPermission($permission);

        $this->assertTrue($role->fresh()->hasPermission("adm/visittransfer/dashboard"));
    }

    /** @test */
    public function itCorrectlyDeterminesThatThisRoleDoesNotHaveASpecificPermissionByAlphaOnlyName()
    {
        $role = factory(\App\Models\Mship\Role::class)->create();
        $permissionA = factory(\App\Models\Mship\Permission::class)->create(["name" => "adm/visit-transfer/dashboard"]);
        $permissionB = factory(\App\Models\Mship\Permission::class)->create(["name" => "adm/visit-transfer/elsewhere"]);
        $permissionC = factory(\App\Models\Mship\Permission::class)->create(["name" => "teamspeak/idle-allowed"]);

        $role->attachPermission($permissionA);

        $this->assertTrue($role->fresh()->hasPermission("adm/visittransfer/dashboard"));
        $this->assertFalse($role->fresh()->hasPermission("adm/visittransfer/elsewhere"));
        $this->assertFalse($role->fresh()->hasPermission("teamspeak/server-admin"));
    }
}