<?php

namespace Tests\Feature\Middleware;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InactivityMiddlewareTest extends TestCase
{
    use DatabaseTransactions;

    private $role;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user->password = 'password';
        $this->role = factory(Role::class)->create(['session_timeout' => 90]);
        $this->user->assignRole($this->role);
    }

    /** @test */
    public function test_a_user_is_not_logged_out_before_session_ends()
    {
        $this->actingAs($this->user)
            ->get(route('mship.manage.dashboard'))
            ->assertSuccessful();

        Carbon::setTestNow(Carbon::now()->addMinutes($this->role->session_timeout - 1));

        $this->actingAs($this->user)
            ->get(route('mship.manage.dashboard'))
            ->assertSuccessful();
    }

    /** @test */
    public function test_a_user_is_logged_out_after_session_ends()
    {
        $this->actingAs($this->user)
            ->get(route('mship.manage.dashboard'))
            ->assertSuccessful();

        Carbon::setTestNow(Carbon::now()->addMinutes($this->role->session_timeout));
        $this->actingAs($this->user)
            ->get(route('mship.manage.dashboard'))
            ->assertRedirect('/dashboard');
    }

    /** @test */
    public function test_a_user_isnt_redirected_to_login_from_public_page_after_session_timeout()
    {
        $this->actingAs($this->user)
            ->get(route('site.home'))
            ->assertSuccessful();

        Carbon::setTestNow(Carbon::now()->addMinutes($this->role->session_timeout));

        $this->actingAs($this->user)
            ->get(route('site.home'))
            ->assertSuccessful();
    }
}
