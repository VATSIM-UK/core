<?php

namespace Tests\Feature;

use App\Models\Mship\Permission;
use App\Models\Mship\State;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminMiddlewareTest extends TestCase
{
    use DatabaseTransactions;

    private $user;
    private $superUser;

    public function setUp()
    {
        parent::setUp();

        $this->user = factory(\App\Models\Mship\Account::class)->create();

        $this->superUser = factory(\App\Models\Mship\Account::class)->create();
        $this->superUser->roles()->attach(\App\Models\Mship\Role::find(1));
    }

    /** @test * */
    public function testAUserWithPermissionCanAccessAnExplicitEndPoint()
    {
        $role = factory(\App\Models\Mship\Role::class)->create();
        $role->permissions()->attach(Permission::find(2)); // GET adm/dashboard
        $this->user->roles()->attach($role);

        $this->actingAs($this->user, 'web')->get(route('adm.dashboard'))->assertSuccessful();
        $this->actingAs($this->superUser, 'web')->get(route('adm.dashboard'))->assertSuccessful();
    }

    /** @test * */
    public function testAUserWithPermissionCanAccessAWildcardEndpoint()
    {
        $role = factory(\App\Models\Mship\Role::class)->create();
        $role->permissions()->attach(Permission::find(6)); // GET adm/mship/account/*
        $this->user->roles()->attach($role);

        $this->actingAs($this->user, 'web')->get(route('adm.mship.account.details',
            $this->user))->assertSuccessful();
        $this->actingAs($this->superUser, 'web')->get(route('adm.mship.account.details',
            $this->user))->assertSuccessful();
    }

    /** @test * */
    public function testAUserWithAnExplicitPermissionCanAccessEndpoint()
    {
        $testAccount = factory(\App\Models\Mship\Account::class)->create();
        $permission = factory(\App\Models\Mship\Permission::class)->create(['name' => "adm/mship/account/{$testAccount->id}/"]);
        $role = factory(\App\Models\Mship\Role::class)->create();
        $role->permissions()->attach($permission->first());
        $this->user->roles()->attach($role);

        $this->actingAs($this->user, 'web')->get(route('adm.mship.account.details',
            $testAccount))->assertSuccessful();
        $this->actingAs($this->superUser, 'web')->get(route('adm.mship.account.details',
            $testAccount))->assertSuccessful();
    }

    /** @test * */
    public function testANonStaffMemberCannotAccessAdmEndpoints()
    {
        $this->actingAs($this->user, 'web')->get(route('adm.mship.feedback.new'))->assertForbidden();
    }
}
