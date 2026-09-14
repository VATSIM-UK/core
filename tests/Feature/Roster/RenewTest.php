<?php

namespace Tests\Feature\Roster;

use App\Libraries\UKCP;
use App\Livewire\Roster\Renew;
use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use App\Models\Mship\State;
use App\Models\NetworkData\Atc;
use App\Models\Roster;
use App\Models\RosterHistory;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Tests\TestCase;

class RenewTest extends TestCase
{
    use DatabaseTransactions;

    private function createEligibleAccount(): Account
    {
        $account = Account::factory()->create();
        $account->addState(State::findByCode('DIVISION'));
        $account->addQualification(Qualification::code('S2')->first());

        return $account;
    }

    private function createAtcSession(Account $account, Carbon $disconnectedAt, int $minutesDuration = 30): Atc
    {
        $atc = Atc::create([
            'account_id' => $account->id,
            'callsign' => 'EGLL_TWR',
            'connected_at' => $disconnectedAt->copy()->subMinutes($minutesDuration),
            'disconnected_at' => $disconnectedAt,
            'qualification_id' => 1,
            'facility_type' => 4,
        ]);

        $atc->minutes_online = $minutesDuration;
        $atc->save();

        return $atc;
    }

    public function test_redirects_when_already_on_roster()
    {
        $account = $this->createEligibleAccount();
        Roster::create(['account_id' => $account->id]);

        $this->mock(UKCP::class)
            ->shouldReceive('getUnreadNotificationsForUser')
            ->andReturn([]);

        Livewire::actingAs($account)
            ->test(Renew::class)
            ->assertRedirect(route('site.roster.index'));
    }

    public function test_redirects_when_not_division_state()
    {
        $account = Account::factory()->create();
        $account->addState(State::findByCode('VISITING'));
        $account->addQualification(Qualification::code('S2')->first());

        $this->mock(UKCP::class)
            ->shouldReceive('getUnreadNotificationsForUser')
            ->andReturn([]);

        Livewire::actingAs($account)
            ->test(Renew::class)
            ->assertRedirect(route('site.roster.index'));
    }

    public function test_redirects_when_no_controller_rating()
    {
        $account = Account::factory()->create();
        $account->addState(State::findByCode('DIVISION'));

        $this->mock(UKCP::class)
            ->shouldReceive('getUnreadNotificationsForUser')
            ->andReturn([]);

        Livewire::actingAs($account)
            ->test(Renew::class)
            ->assertRedirect(route('site.roster.index'));
    }

    public function test_mounts_with_page_one()
    {
        $account = $this->createEligibleAccount();

        $this->mock(UKCP::class)
            ->shouldReceive('getUnreadNotificationsForUser')
            ->andReturn([]);

        Livewire::actingAs($account)
            ->test(Renew::class)
            ->assertSet('page', 1);
    }

    public function test_cannot_proceed_with_no_connection_history()
    {
        $account = $this->createEligibleAccount();

        $this->mock(UKCP::class)
            ->shouldReceive('getUnreadNotificationsForUser')
            ->andReturn([]);

        Livewire::actingAs($account)
            ->test(Renew::class)
            ->call('nextPage')
            ->assertForbidden();
    }

    public function test_reactivate_creates_roster_record()
    {
        $account = $this->createEligibleAccount();

        $this->mock(UKCP::class)
            ->shouldReceive('getUnreadNotificationsForUser')
            ->andReturn([]);

        $this->createAtcSession($account, Carbon::now()->subMonths(6));

        Livewire::actingAs($account)
            ->test(Renew::class)
            ->call('nextPage')
            ->call('reactivate')
            ->assertRedirect(route('site.roster.index'));

        $this->assertDatabaseHas('roster', ['account_id' => $account->id]);
    }

    public function test_shows_cannot_reactivate_message_when_connection_too_old()
    {
        $account = $this->createEligibleAccount();

        $this->mock(UKCP::class)
            ->shouldReceive('getUnreadNotificationsForUser')
            ->andReturn([]);

        $this->createAtcSession($account, Carbon::now()->subMonths(19));

        Livewire::actingAs($account)
            ->test(Renew::class)
            ->assertSee('cannot automatically reactivate');
    }

    public function test_shows_last_logon_on_first_page()
    {
        $account = $this->createEligibleAccount();

        $this->mock(UKCP::class)
            ->shouldReceive('getUnreadNotificationsForUser')
            ->andReturn([]);

        $this->createAtcSession($account, Carbon::now()->subMonths(6));

        Livewire::actingAs($account)
            ->test(Renew::class)
            ->assertSee('last controlling session was');
    }

    public function test_shows_notification_count()
    {
        $account = $this->createEligibleAccount();
        $this->createAtcSession($account, Carbon::now()->subMonths(6));

        $this->mock(UKCP::class)
            ->shouldReceive('getUnreadNotificationsForUser')
            ->andReturn([
                ['id' => 1, 'title' => 'Change 1', 'body' => 'Body 1', 'link' => null],
            ]);

        Livewire::actingAs($account)
            ->test(Renew::class)
            ->assertSee('You have 1 notifications to read.');
    }

    public function test_cannot_proceed_when_previous_removal_and_no_hours_in_last_two_quarters()
    {
        $account = $this->createEligibleAccount();

        RosterHistory::create([
            'account_id' => $account->id,
            'original_created_at' => Carbon::now()->subMonths(12),
            'original_updated_at' => Carbon::now()->subMonths(12),
            'created_at' => Carbon::now()->subMonths(9),
        ]);

        $this->mock(UKCP::class)
            ->shouldReceive('getUnreadNotificationsForUser')
            ->andReturn([]);

        $this->createAtcSession($account, Carbon::now()->subMonths(15));

        Livewire::actingAs($account)
            ->test(Renew::class)
            ->call('nextPage')
            ->assertForbidden();
    }

    public function test_can_proceed_when_previous_removal_and_met_hours_in_last_quarter()
    {
        $account = $this->createEligibleAccount();

        RosterHistory::create([
            'account_id' => $account->id,
            'original_created_at' => Carbon::now()->subMonths(12),
            'original_updated_at' => Carbon::now()->subMonths(12),
        ]);

        $currentQuarterStart = Carbon::now()->copy()->startOfQuarter();
        $lastQuarterStart = $currentQuarterStart->copy()->subMonths(3);

        $sessionTime = $lastQuarterStart->copy()->addWeeks(4)->addHours(12);

        $this->mock(UKCP::class)
            ->shouldReceive('getUnreadNotificationsForUser')
            ->andReturn([]);

        $this->createAtcSession($account, $sessionTime, 180);

        Livewire::actingAs($account)
            ->test(Renew::class)
            ->call('nextPage')
            ->assertSet('page', 2);
    }

    public function test_can_proceed_when_previous_removal_and_met_hours_in_prior_quarter_only()
    {
        $account = $this->createEligibleAccount();

        RosterHistory::create([
            'account_id' => $account->id,
            'original_created_at' => Carbon::now()->subMonths(12),
            'original_updated_at' => Carbon::now()->subMonths(12),
        ]);

        $currentQuarterStart = Carbon::now()->copy()->startOfQuarter();

        $sessionTime = $currentQuarterStart->copy()->subMonths(5)->addWeeks(2)->addHours(12);

        $this->mock(UKCP::class)
            ->shouldReceive('getUnreadNotificationsForUser')
            ->andReturn([]);

        $this->createAtcSession($account, $sessionTime, 180);

        Livewire::actingAs($account)
            ->test(Renew::class)
            ->call('nextPage')
            ->assertSet('page', 2);
    }

    public function test_shows_quarter_failure_message_when_no_hours_in_last_two_quarters()
    {
        $account = $this->createEligibleAccount();

        RosterHistory::create([
            'account_id' => $account->id,
            'original_created_at' => Carbon::now()->subMonths(12),
            'original_updated_at' => Carbon::now()->subMonths(12),
            'created_at' => Carbon::now()->subMonths(9),
        ]);

        $this->mock(UKCP::class)
            ->shouldReceive('getUnreadNotificationsForUser')
            ->andReturn([]);

        $this->createAtcSession($account, Carbon::now()->subMonths(15));

        Livewire::actingAs($account)
            ->test(Renew::class)
            ->assertSee('minimum controlling requirements');
    }

    public function test_first_time_joiner_not_blocked_by_quarter_check()
    {
        $account = $this->createEligibleAccount();

        $this->mock(UKCP::class)
            ->shouldReceive('getUnreadNotificationsForUser')
            ->andReturn([]);

        $this->createAtcSession($account, Carbon::now()->subMonths(6));

        Livewire::actingAs($account)
            ->test(Renew::class)
            ->call('nextPage')
            ->assertSet('page', 2);
    }
}
