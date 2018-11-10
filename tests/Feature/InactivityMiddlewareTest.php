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

    private function login()
    {
        Auth::guard('web')->login($this->user);
        Auth::guard('vatsim-sso')->login($this->user);
    }

    /** @test * */
    public function testAUserIsNotLoggedOutBeforeSessionEnds()
    {
        $this->login();
        $this->get(route('mship.manage.dashboard'))->assertSuccessful();
        Carbon::setTestNow(Carbon::now()->addMinutes($this->role->session_timeout - 1));
        $this->get(route('mship.manage.dashboard'))->assertSuccessful();
        Carbon::setTestNow();
    }

    /** @test * */
    public function testAUserIsLoggedOutAfterSessionEnds()
    {
        $this->login();
        $this->get(route('mship.manage.dashboard'))->assertSuccessful();
        Carbon::setTestNow(Carbon::now()->addMinutes($this->role->session_timeout));
        $this->get(route('mship.manage.dashboard'))->assertRedirect('/login');
        Carbon::setTestNow();
    }

    /** @test * */
    public function testAUserIsntRedirectedToLoginFromPublicPageAfterSessionTimeout()
    {
        $this->login();
        $this->get(route('site.home'))->assertSuccessful();
        Carbon::setTestNow(Carbon::now()->addMinutes($this->role->session_timeout));
        $this->get(route('site.home'))->assertSuccessful();
        Carbon::setTestNow();
    }
}
