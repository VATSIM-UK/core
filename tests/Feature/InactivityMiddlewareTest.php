<?php

namespace Tests\Feature;

use App\Models\Mship\Account;
use App\Models\Mship\Role;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class InactivityMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $role;

    public function setUp()
    {
        parent::setUp();

        $this->user = factory(Account::class, 'withQualification')->create(['password' => 'password']);
        $this->role = factory(Role::class)->create();
        $this->user->roles()->attach($this->role);
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
        $this->get(route('mship.manage.dashboard'))->assertRedirect(route('login'));
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
