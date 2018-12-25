<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Mship\Account;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Foundation\Testing\DatabaseTransactions;

class AdminMiddlewareTest extends TestCase
{
    use DatabaseTransactions;

    /** @test * */
    public function testAGuestCannotAccessAdmEndpoints()
    {
        $this->get(route('adm.mship.feedback.new'))
                ->assertRedirect(route('login'));
    }

    /** @test * */
    public function testANonStaffMemberCannotAccessAdmEndpoints()
    {
        $user = factory(Account::class)->create();

        $this->actingAs($user)->get('adm/dashboard')
                ->assertForbidden();
    }

    /** @test */
    public function testPrivaccCanBypassGuard()
    {
        $this->actingAs($this->privacc)
                ->get('adm/dashboard')
                ->assertSuccessful();
    }

//    public function testUsingEndpointPermissionsAllowsAccess()
//    {
//        $staff = factory(Account::class)->create();
//
//        $this->actingAs($staff)
//                ->get('adm/dashboard')
//                ->assertForbidden();
//
//        $role = factory(Role::class)->create();
//        $permission = Permission::findByName('adm/dashboard');
//        $role->givePermissionTo($permission);
//        $staff->assignRole($role);
//
//        $this->actingAs($staff->fresh())
//            ->get('adm/dashboard')
//            ->assertSuccessful()
//            ->assertSee('Administration Control Panel');
//
//        $this->actingAs($staff->fresh())
//            ->get('adm/mship/account/account')
//            ->assertForbidden();
//    }

    /** @test **/
    public function testPrivAccDoesntWorkInProduction()
    {
        config()->set('app.env', 'production');

        $this->actingAs($this->privacc)
            ->get('adm/dashboard')
            ->assertForbidden();
    }
}
