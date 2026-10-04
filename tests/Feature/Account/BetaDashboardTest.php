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

    public function test_authenticated_user_can_view_the_default_dashboard(): void
    {
        $this->actingAs($this->user)
            ->get(route('mship.manage.dashboard'))
            ->assertOk()
            ->assertSee($this->user->name)
            ->assertSee('Personal Details', false)
            ->assertSee('ATC rating', false)
            ->assertSee('Pilot rating', false);
    }

    public function test_default_dashboard_shows_current_atc_rating(): void
    {
        $this->actingAs($this->user)
            ->get(route('mship.manage.dashboard'))
            ->assertOk()
            ->assertSee($this->user->qualification_atc->code, false)
            ->assertSee($this->user->qualification_atc->name_long, false);
    }

    public function test_deprecated_classic_dashboard_is_still_accessible(): void
    {
        $this->actingAs($this->user)
            ->get(route('mship.manage.dashboard.classic'))
            ->assertOk()
            ->assertSee($this->user->name)
            ->assertSee('Discord Registration', false);
    }

    public function test_legacy_beta_url_redirects_to_the_default_dashboard(): void
    {
        $this->actingAs($this->user)
            ->get(route('mship.manage.dashboard.beta'))
            ->assertRedirect(route('mship.manage.dashboard'));
    }
}
