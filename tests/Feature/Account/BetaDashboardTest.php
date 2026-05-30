<?php

namespace Tests\Feature\Account;

use App\Libraries\UKCP;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Collection;
use Mockery\MockInterface;
use Tests\TestCase;

class BetaDashboardTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mock(UKCP::class, function (MockInterface $mock) {
            $mock->shouldReceive('getValidTokensFor')
                ->andReturn(Collection::make());
        });
    }

    public function test_authenticated_user_can_view_beta_dashboard(): void
    {
        $this->actingAs($this->user)
            ->get(route('mship.manage.dashboard.beta'))
            ->assertOk()
            ->assertSee('beta dashboard', false)
            ->assertSee($this->user->name, false)
            ->assertSee('Switch to classic dashboard', false)
            ->assertSee('ATC rating', false)
            ->assertSee('Pilot rating', false);
    }

    public function test_personal_details_shows_current_atc_rating(): void
    {
        $this->actingAs($this->user)
            ->get(route('mship.manage.dashboard.beta'))
            ->assertOk()
            ->assertSee($this->user->qualification_atc->code, false)
            ->assertSee($this->user->qualification_atc->name_long, false);
    }

    public function test_beta_dashboard_includes_link_to_classic_dashboard(): void
    {
        $this->actingAs($this->user)
            ->get(route('mship.manage.dashboard.beta'))
            ->assertOk()
            ->assertSee(route('mship.manage.dashboard'), false);
    }

    public function test_classic_dashboard_includes_link_to_beta_dashboard(): void
    {
        $this->actingAs($this->user)
            ->get(route('mship.manage.dashboard'))
            ->assertOk()
            ->assertSee(route('mship.manage.dashboard.beta'), false);
    }
}
