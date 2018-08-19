<?php

namespace Tests\Feature;

use App\Models\Mship\Account;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SmartcarsTest extends TestCase
{
    //use RefreshDatabase;

    private $account;

    public function setUp()
    {
        parent::setUp();

        $this->account = factory(Account::class)->create();
    }

    /** @test * */
    public function itRedirectsFromDashboardAsGuest()
    {
        $this->get(route('fte.dashboard'))
            ->assertRedirect(route('login'));
    }

    /** @test * */
    public function itLoadsTheDashboard()
    {
        $this->actingAs($this->account, 'web')->get(route('fte.dashboard'))
            ->assertSuccessful();
    }
}
