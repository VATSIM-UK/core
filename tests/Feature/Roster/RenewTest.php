<?php

namespace Tests\Feature\Roster;

use App\Libraries\UKCP;
use App\Livewire\Roster\Renew;
use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use App\Models\Mship\State;
use App\Models\NetworkData\Atc;
use App\Models\Roster;
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

    private function createAtcSession(Account $account, Carbon $disconnectedAt): Atc
    {
        return Atc::create([
            'account_id' => $account->id,
            'callsign' => 'EGLL_TWR',
            'connected_at' => $disconnectedAt->copy()->subMinutes(30),
            'disconnected_at' => $disconnectedAt,
            'qualification_id' => 1,
            'facility_type' => 4,
        ]);
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

    public function test_cannot_proceed_without_recent_connection()
    {
        $account = $this->createEligibleAccount();

        $this->mock(UKCP::class)
            ->shouldReceive('getUnreadNotificationsForUser')
            ->andReturn([]);

        $this->createAtcSession($account, Carbon::now()->subMonths(19));

        Livewire::actingAs($account)
            ->test(Renew::class)
            ->call('nextPage')
            ->assertForbidden();
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

    public function test_can_proceed_with_recent_connection()
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

    public function test_can_proceed_when_within_18_month_window()
    {
        $account = $this->createEligibleAccount();

        $this->mock(UKCP::class)
            ->shouldReceive('getUnreadNotificationsForUser')
            ->andReturn([]);

        $this->createAtcSession($account, Carbon::now()->subMonths(18)->addDay());

        Livewire::actingAs($account)
            ->test(Renew::class)
            ->call('nextPage')
            ->assertSet('page', 2);
    }

    public function test_cannot_reactivate_without_recent_connection()
    {
        $account = $this->createEligibleAccount();

        $this->mock(UKCP::class)
            ->shouldReceive('getUnreadNotificationsForUser')
            ->andReturn([]);

        Livewire::actingAs($account)
            ->test(Renew::class)
            ->call('reactivate')
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

    public function test_mark_notification_read_removes_notification()
    {
        $account = $this->createEligibleAccount();
        $this->createAtcSession($account, Carbon::now()->subMonths(6));

        $ukcp = $this->mock(UKCP::class);
        $ukcp->shouldReceive('getUnreadNotificationsForUser')
            ->andReturn([
                ['id' => 1, 'title' => 'Change 1', 'body' => 'Body 1', 'link' => null],
            ]);
        $ukcp->shouldReceive('markNotificationReadForUser')
            ->with(\Mockery::type(Account::class), 1)
            ->andReturn(true);

        Livewire::actingAs($account)
            ->test(Renew::class)
            ->assertSee('You have 1 notifications to read.')
            ->call('markNotificationRead', 1, 1)
            ->assertSee('You have 0 notifications to read.');
    }

    public function test_mark_notification_read_failure_retains_notification()
    {
        $account = $this->createEligibleAccount();
        $this->createAtcSession($account, Carbon::now()->subMonths(6));

        $ukcp = $this->mock(UKCP::class);
        $ukcp->shouldReceive('getUnreadNotificationsForUser')
            ->andReturn([
                ['id' => 1, 'title' => 'Change 1', 'body' => 'Body 1', 'link' => null],
            ]);
        $ukcp->shouldReceive('markNotificationReadForUser')
            ->with(\Mockery::type(Account::class), 1)
            ->andReturn(false);

        Livewire::actingAs($account)
            ->test(Renew::class)
            ->assertSee('You have 1 notifications to read.')
            ->call('markNotificationRead', 1, 1)
            ->assertSee('You have 1 notifications to read.');
    }

    public function test_shows_page_two_after_proceeding()
    {
        $account = $this->createEligibleAccount();

        $this->mock(UKCP::class)
            ->shouldReceive('getUnreadNotificationsForUser')
            ->andReturn([]);

        $this->createAtcSession($account, Carbon::now()->subMonths(6));

        Livewire::actingAs($account)
            ->test(Renew::class)
            ->call('nextPage')
            ->assertSee('Reactivate')
            ->assertSee('Add to');
    }
}
