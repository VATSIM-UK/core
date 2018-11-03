<?php

namespace Tests\Feature;

use App\Models\Mship\Account;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
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
        $this->superUser->assignRole(Role::findById(1));
    }

    private function createRoleWithPermissionName(string $permission, Account $user)
    {
        $role = factory(Role::class)->create();
        $role->givePermissionTo(Permission::findByName($permission));
        $user->assignRole($role);
    }

    /** @test * */
    public function testAUserWithPermissionCanAccessAnExplicitEndPoint()
    {
        $this->createRoleWithPermissionName('adm/dashboard', $this->user); // GET adm/dashboard

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
