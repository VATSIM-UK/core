<?php

namespace Tests\Feature;

use App\Models\Mship\Account;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
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
        $this->superUser->assignRole(Role::findByName('privacc'));
    }

    private function createRoleWithPermissionName(string $permission, Account &$user)
    {
        $role = factory(Role::class)->create();
        $role->givePermissionTo(Permission::findByName($permission));
        $user->assignRole($role->fresh());
    }

    /** @test * */
    public function testAUserWithPermissionCanAccessAnExplicitEndPoint()
    {
        $this->createRoleWithPermissionName('adm/dashboard', $this->user);

        $this->actingAs($this->user->fresh(), 'web')->get(route('adm.dashboard'))->assertSuccessful();
        $this->actingAs($this->superUser->fresh(), 'web')->get(route('adm.dashboard'))->assertSuccessful();
    }

    /** @test * */
    public function testAUserWithPermissionCanAccessAWildcardEndpoint()
    {
        $this->createRoleWithPermissionName('adm/mship/account/*', $this->user);

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
        $role->givePermissionTo($permission);
        $this->user->assignRole($role->fresh());

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
