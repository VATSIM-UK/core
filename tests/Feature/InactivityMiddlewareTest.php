<?php

namespace Tests\Feature;

use App\Models\Mship\Account;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InactivityMiddlewareTest extends TestCase
{
    use DatabaseTransactions;

    private $user;
    private $role;

    public function setUp()
    {
        parent::setUp();

        $this->user = factory(Account::class)->create(['password' => 'password']);
        $this->role = factory(Role::class)->create(['session_timeout' => 90]);
        $this->user->assignRole($this->role);
    }

    /** @test * */
    public function testAUserIsNotLoggedOutBeforeSessionEnds()
    {
        $this->actingAs($this->user)
            ->get(route('mship.manage.dashboard'))->assertSuccessful();

        Carbon::setTestNow(Carbon::now()->addMinutes($this->role->session_timeout - 1));

        $this->actingAs($this->user)
            ->get(route('mship.manage.dashboard'))->assertSuccessful();
    }

    /** @test * */
    public function testAUserIsLoggedOutAfterSessionEnds()
    {
        $this->actingAs($this->user)
            ->get(route('mship.manage.dashboard'))->assertSuccessful();

        Carbon::setTestNow(Carbon::now()->addMinutes($this->role->session_timeout));

        $this->actingAs($this->user)
            ->get(route('mship.manage.dashboard'))->assertRedirect('/login');
    }

    /** @test * */
    public function testAUserIsntRedirectedToLoginFromPublicPageAfterSessionTimeout()
    {
        $this->actingAs($this->user)
            ->get(route('site.home'))->assertSuccessful();

        Carbon::setTestNow(Carbon::now()->addMinutes($this->role->session_timeout));

        $this->actingAs($this->user)
            ->get(route('site.home'))->assertSuccessful();
    }
}
