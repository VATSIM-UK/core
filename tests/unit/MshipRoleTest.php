<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;

class MshipRoleTest extends TestCase
{
    use DatabaseTransactions;

    /** @test **/
    public function it_stores_role_details()
    {
        $role = factory(\App\Models\Mship\Role::class)->create();

        $this->seeInDatabase("mship_role", [
            "role_id" => $role->role_id,
        ]);
    }

    /** @test **/
    public function it_correctly_determines_if_the_password_is_mandatory()
    {
        $role = factory(\App\Models\Mship\Role::class)->create(["password_mandatory" => true]);

        $this->assertTrue($role->hasMandatoryPassword());
    }

    /** @test **/
    public function it_correctly_determines_if_a_password_lifetime_exists()
    {
        $role = factory(\App\Models\Mship\Role::class)->create(["password_lifetime" => 30]);

        $this->assertTrue($role->hasPasswordLifetime());
    }

    /** @test **/
    public function it_correctly_determines_that_the_role_has_a_session_expiry()
    {
        $role = factory(\App\Models\Mship\Role::class)->create(["session_timeout" => 60]);

        $this->assertTrue($role->hasTimeout($role));

        $this->seeInDatabase("mship_role", [
            "role_id" => $role->role_id,
            "session_timeout" => 60,
        ]);
    }

    /** @test **/
    public function it_correctly_loads_the_default_role()
    {
        $role = factory(\App\Models\Mship\Role::class)->create(["default" => true]);

        $defaultRole = \App\Models\Mship\Role::findDefault();

        $this->assertEquals($role->role_id, $defaultRole->role_id);
    }

    /** @test **/
    public function it_correctly_removes_the_default_status_from_the_old_default_role_when_creating_a_new_default_role()
    {
        $roleOriginalDefault = factory(\App\Models\Mship\Role::class)->create(["default" => true]);

        $roleNewDefault = factory(\App\Models\Mship\Role::class)->create();
        $roleNewDefault->default = 1;
        $roleNewDefault->save();

        $this->assertFalse($roleOriginalDefault->fresh()->is_default);
        $this->assertTrue($roleNewDefault->fresh()->is_default);
    }
}