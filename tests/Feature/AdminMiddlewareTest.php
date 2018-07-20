<?php

namespace Tests\Feature;

use App\Models\Mship\Account;
use App\Models\Mship\Permission;
use App\Models\Mship\Role;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminMiddlewareTest extends TestCase
{
    use DatabaseTransactions;

    private $user;
    private $otherUser;
    private $superUser;

    public function setUp()
    {
        parent::setUp();

        $this->user = factory(Account::class)->create();

        $this->otherUser = factory(Account::class)->create();

        $this->superUser = factory(Account::class)->create();
        $this->superUser->roles()->attach(Role::find(1));
    }

    private function createRoleWithPermissionId(int $permission, $user)
    {
        $role = factory(Role::class)->create();
        $role->permissions()->attach(Permission::find($permission));
        $user->roles()->attach($role);
    }

    /** @test * */
    public function testAUserWithPermissionCanAccessAnExplicitEndPoint()
    {
        $this->createRoleWithPermissionId(2, $this->user); // GET adm/dashboard

        $this->actingAs($this->user, 'web')->get(route('adm.dashboard'))->assertSuccessful();
        $this->actingAs($this->superUser, 'web')->get(route('adm.dashboard'))->assertSuccessful();
    }

    /** @test * */
    public function testAUserWithPermissionCanAccessAWildcardEndpoint()
    {
        $this->createRoleWithPermissionId(6, $this->user); // GET adm/mship/account/*

        $this->actingAs($this->user, 'web')->get(route('adm.mship.account.details',
            $this->user))->assertSuccessful();
        $this->actingAs($this->superUser, 'web')->get(route('adm.mship.account.details',
            $this->user))->assertSuccessful();
    }

    /** @test * */
    public function testAUserWithAnExplicitPermissionCanAccessEndpoint()
    {
        $permission = factory(Permission::class)->create(['name' => "adm/mship/account/{$this->otherUser->id}/"]);
        $role = factory(Role::class)->create();
        $role->permissions()->attach($permission->first());
        $this->user->roles()->attach($role);

        $this->actingAs($this->user, 'web')->get(route('adm.mship.account.details',
            $this->otherUser))->assertSuccessful();
        $this->actingAs($this->superUser, 'web')->get(route('adm.mship.account.details',
            $this->otherUser))->assertSuccessful();
    }

    /** @test * */
    public function testANonStaffMemberCannotAccessAdmEndpoints()
    {
        $this->actingAs($this->user, 'web')->get(route('adm.mship.feedback.new'))->assertForbidden();
    }
}
