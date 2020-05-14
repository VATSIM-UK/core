<?php

namespace Tests\Feature\Middleware;

use App\Models\Mship\Account;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminMiddlewareTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function testAGuestCannotAccessAdmEndpoints()
    {
        $this->get(route('adm.index'))
            ->assertRedirect(route('login'));
    }

    /** @test */
    public function testANonStaffMemberCannotAccessAdmEndpoints()
    {
        $this->actingAs($this->user)->get('adm')
            ->assertForbidden();
    }

    /** @test */
    public function testPrivaccCanBypassGuard()
    {
        $this->actingAs($this->privacc)
            ->get('adm')
            ->assertSuccessful();
    }

    /** @test */
    public function testPrivaccDoesntWorkInProduction()
    {
        config()->set('app.env', 'production');

        $this->actingAs($this->privacc)
            ->get('adm')
            ->assertForbidden();
    }

    /** @test */
    public function testTelescopeIsNotAvailableToGuests()
    {
        $this->get(config('telescope.path'))
            ->assertRedirect(route('login'));
    }

    /** @test */
    public function testTelescopeIsNotAvailableToNormalUsers()
    {
        $this->actingAs($this->user)
            ->get(config('telescope.path'))
            ->assertForbidden();
    }

    /** @test */
    public function testTelescopeIsAvailableToAuthorisedUsers()
    {
        $admin = factory(Account::class)->create();
        $admin->givePermissionTo('telescope');

        $this->actingAs($admin)
            ->get(config('telescope.path'))
            ->assertSuccessful();

        $this->actingAs($this->privacc)
            ->get(config('telescope.path'))
            ->assertSuccessful();
    }

    /** @test */
    public function testHorizonIsNotAvailableToGuests()
    {
        $this->get(config('horizon.path'))
            ->assertRedirect(route('login'));
    }

    /** @test */
    public function testHorizonIsNotAvailableToNormalUsers()
    {
        $this->actingAs($this->user)
            ->get(config('horizon.path'))
            ->assertForbidden();
    }

    /** @test */
    public function testHorizonIsAvailableToAuthorisedUsers()
    {
        $admin = factory(Account::class)->create();
        $admin->givePermissionTo('horizon');

        $this->actingAs($admin)
            ->get(config('horizon.path'))
            ->assertSuccessful();

        $this->actingAs($this->privacc)
            ->get(config('horizon.path'))
            ->assertSuccessful();
    }
}
