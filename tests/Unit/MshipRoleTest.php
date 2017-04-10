<?php

namespace Tests\Unit;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MshipRoleTest extends TestCase
{
    use DatabaseTransactions;

    /** @test * */
    public function it_stores_role_details()
    {
        $role = factory(\App\Models\Mship\Role::class)->create();

        $this->seeInDatabase("mship_role", [
            "id" => $role->id,
        ]);
    }

    /** @test * */
    public function it_correctly_determines_if_the_password_is_mandatory()
    {
        $role = factory(\App\Models\Mship\Role::class)->create(["password_mandatory" => true]);

        $this->assertTrue($role->hasMandatoryPassword());
    }

    /** @test * */
    public function it_correctly_determines_if_a_password_lifetime_exists()
    {
        $role = factory(\App\Models\Mship\Role::class)->create(["password_lifetime" => 30]);

        $this->assertTrue($role->hasPasswordLifetime());
    }

    /** @test * */
    public function it_correctly_determines_that_the_role_has_a_session_timeout()
    {
        $role = factory(\App\Models\Mship\Role::class)->create(["session_timeout" => 60]);

        $this->assertTrue($role->hasSessionTimeout());

        $this->seeInDatabase("mship_role", [
            "id"              => $role->id,
            "session_timeout" => 60,
        ]);
    }

    /** @test * */
    public function it_correctly_loads_the_default_role()
    {
        $role = factory(\App\Models\Mship\Role::class)->create(["default" => true]);

        $defaultRole = \App\Models\Mship\Role::findDefault();

        $this->assertEquals($role->id, $defaultRole->id);
    }

    /** @test * */
    public function it_correctly_removes_the_default_status_from_the_old_default_role_when_creating_a_new_default_role()
    {
        $roleOriginalDefault = factory(\App\Models\Mship\Role::class)->create(["default" => true]);

        $roleNewDefault = factory(\App\Models\Mship\Role::class)->create();
        $roleNewDefault->default = 1;
        $roleNewDefault->save();

        $this->assertFalse($roleOriginalDefault->fresh()->is_default);
        $this->assertTrue($roleNewDefault->fresh()->is_default);
    }

    /** @test */
    public function it_correctly_determines_that_this_role_has_a_specific_permission()
    {
        $role = factory(\App\Models\Mship\Role::class)->create();
        $permission = factory(\App\Models\Mship\Permission::class)->create(["name" => "adm/visit-transfer/dashboard"]);

        $role->attachPermission($permission);

        $this->seeInDatabase("mship_permission_role", [
            "role_id"       => $role->id,
            "permission_id" => $permission->id,
        ]);

        $this->assertTrue($role->fresh()->hasPermission($permission));
        $this->assertTrue($role->fresh()->hasPermission($permission->name));
    }

    /** @test */
    public function it_correctly_determines_that_this_role_does_not_have_a_specific_permission()
    {
        $role = factory(\App\Models\Mship\Role::class)->create();
        $permissionA = factory(\App\Models\Mship\Permission::class)->create(["name" => "adm/visit-transfer/dashboard"]);
        $permissionB = factory(\App\Models\Mship\Permission::class)->create(["name" => "adm/visit-transfer/elsewhere"]);

        $role->attachPermission($permissionA);

        $this->seeInDatabase("mship_permission_role", [
            "role_id"       => $role->id,
            "permission_id" => $permissionA->id,
        ]);

        $this->notseeInDatabase("mship_permission_role", [
            "role_id"       => $role->id,
            "permission_id" => $permissionB->id,
        ]);

        $this->assertTrue($role->fresh()->hasPermission($permissionA));
        $this->assertTrue($role->fresh()->hasPermission($permissionA->name));
        $this->assertFalse($role->fresh()->hasPermission($permissionB));
        $this->assertFalse($role->fresh()->hasPermission($permissionB->name));
    }

    /** @test */
    public function it_correctly_determines_that_this_role_has_a_specific_permission_by_alpha_only_name()
    {
        $role = factory(\App\Models\Mship\Role::class)->create();
        $permission = factory(\App\Models\Mship\Permission::class)->create(["name" => "adm/visit-transfer/dashboard"]);

        $role->attachPermission($permission);

        $this->assertTrue($role->fresh()->hasPermission("adm/visittransfer/dashboard"));
    }

    /** @test */
    public function it_correctly_determines_that_this_role_does_not_have_a_specific_permission_by_alpha_only_name()
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