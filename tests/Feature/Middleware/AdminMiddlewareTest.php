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
            ->assertRedirect(route('landing'));
    }

    /** @test */
    public function testANonStaffMemberCannotAccessAdmEndpoints()
    {
        $this->actingAs($this->user)->get(route('adm.index'))
            ->assertForbidden();
    }

    /** @test */
    public function testPrivaccCanBypassGuard()
    {
        $this->actingAs($this->privacc)
            ->get(route('adm.index'))
            ->assertSuccessful();
    }

    /** @test */
    public function testTelescopeIsNotAvailableToGuests()
    {
        $this->get(config('telescope.path'))
            ->assertRedirect(route('landing'));
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
        $admin = Account::factory()->create();
        $admin->givePermissionTo('telescope.access');

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
            ->assertRedirect(route('landing'));
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
        $admin = Account::factory()->create();
        $admin->givePermissionTo('horizon.access');

        $this->actingAs($admin)
            ->get(config('horizon.path'))
            ->assertSuccessful();

        $this->actingAs($this->privacc)
            ->get(config('horizon.path'))
            ->assertSuccessful();
    }
}
