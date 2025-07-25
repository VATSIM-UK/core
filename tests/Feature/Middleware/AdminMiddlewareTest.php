<?php

namespace Tests\Feature\Middleware;

use App\Models\Mship\Account;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AdminMiddlewareTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function test_a_guest_cannot_access_adm_endpoints()
    {
        $this->get(route('adm.index'))
            ->assertRedirect(route('landing'));
    }

    #[Test]
    public function test_a_non_staff_member_cannot_access_adm_endpoints()
    {
        $this->actingAs($this->user)->get(route('adm.index'))
            ->assertForbidden();
    }

    #[Test]
    public function test_privacc_can_bypass_guard()
    {
        $this->actingAs($this->privacc)
            ->get(route('adm.index'))
            ->assertSuccessful();
    }

    #[Test]
    public function test_telescope_is_not_available_to_guests()
    {
        $this->get(config('telescope.path'))
            ->assertRedirect(route('landing'));
    }

    #[Test]
    public function test_telescope_is_not_available_to_normal_users()
    {
        $this->actingAs($this->user)
            ->get(config('telescope.path'))
            ->assertForbidden();
    }

    #[Test]
    public function test_telescope_is_available_to_authorised_users()
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

    #[Test]
    public function test_horizon_is_not_available_to_guests()
    {
        $this->get(config('horizon.path'))
            ->assertRedirect(route('landing'));
    }

    #[Test]
    public function test_horizon_is_not_available_to_normal_users()
    {
        $this->actingAs($this->user)
            ->get(config('horizon.path'))
            ->assertForbidden();
    }

    #[Test]
    public function test_horizon_is_available_to_authorised_users()
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
