<?php

namespace Tests\Unit\VisitTransfer;

use App\Models\Mship\Account;
use App\Models\Mship\State;
use App\Models\Roster;
use App\Models\RosterHistory;
use App\Services\VisitTransfer\VisitingControllerInactivity;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class VisitingControllerInactivityTest extends TestCase
{
    private VisitingControllerInactivity $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new VisitingControllerInactivity;
    }

    #[Test]
    public function it_flags_a_visiting_controller_inactive_for_six_months(): void
    {
        $account = $this->makeVisitingController(now()->subYear());
        $this->recordRosterRemoval($account, now()->subMonths(7));

        $this->assertSame(VisitingControllerInactivity::REASON_SIX_MONTHS, $this->service->reasonFor($account));
    }

    #[Test]
    public function it_does_not_flag_a_visiting_controller_inactive_for_less_than_six_months(): void
    {
        $account = $this->makeVisitingController(now()->subYear());
        $this->recordRosterRemoval($account, now()->subMonths(3));

        $this->assertNull($this->service->reasonFor($account));
    }

    #[Test]
    public function it_flags_a_visiting_controller_inactive_twice_in_two_years(): void
    {
        $account = $this->makeVisitingController(now()->subYears(2));
        Roster::create(['account_id' => $account->id]);

        $this->recordRosterRemoval($account, now()->subMonths(13));
        $this->recordRosterRemoval($account, now()->subMonths(2));

        $this->assertSame(VisitingControllerInactivity::REASON_TWICE_IN_TWO_YEARS, $this->service->reasonFor($account));
    }

    #[Test]
    public function it_does_not_flag_a_visiting_controller_currently_on_the_roster_with_a_single_old_removal(): void
    {
        $account = $this->makeVisitingController(now()->subYear());
        Roster::create(['account_id' => $account->id]);
        $this->recordRosterRemoval($account, now()->subMonths(8));

        $this->assertNull($this->service->reasonFor($account));
    }

    #[Test]
    public function it_ignores_removals_that_predate_the_current_visiting_state(): void
    {
        $account = $this->makeVisitingController(now()->subMonths(2));
        $this->recordRosterRemoval($account, now()->subMonths(8));

        $this->assertNull($this->service->reasonFor($account));
    }

    #[Test]
    public function it_ignores_removals_older_than_two_years(): void
    {
        $account = $this->makeVisitingController(now()->subYears(4));
        Roster::create(['account_id' => $account->id]);

        $this->recordRosterRemoval($account, now()->subYears(3));
        $this->recordRosterRemoval($account, now()->subMonths(25));

        $this->assertNull($this->service->reasonFor($account));
    }

    #[Test]
    public function it_ignores_manual_roster_removals(): void
    {
        $account = $this->makeVisitingController(now()->subYear());

        RosterHistory::create([
            'account_id' => $account->id,
            'original_created_at' => now()->subMonths(7),
            'original_updated_at' => now()->subMonths(7),
            'removed_by' => $this->user->id,
            'roster_update_id' => null,
        ]);

        $this->assertNull($this->service->reasonFor($account));
    }

    #[Test]
    public function it_does_not_flag_accounts_without_a_visiting_state(): void
    {
        $account = Account::factory()->create();
        $account->addState(State::findByCode('DIVISION'));

        $this->recordRosterRemoval($account, now()->subMonths(7));
        $this->recordRosterRemoval($account, now()->subMonths(2));

        $this->assertNull($this->service->reasonFor($account));
    }

    #[Test]
    public function it_only_returns_eligible_visiting_controllers(): void
    {
        $eligible = $this->makeVisitingController(now()->subYear());
        $this->recordRosterRemoval($eligible, now()->subMonths(7));

        $ineligible = $this->makeVisitingController(now()->subYear());
        $this->recordRosterRemoval($ineligible, now()->subMonths(2));

        $results = $this->service->eligible();

        $this->assertCount(1, $results);
        $this->assertSame($eligible->id, $results->first()['account']->id);
        $this->assertSame(VisitingControllerInactivity::REASON_SIX_MONTHS, $results->first()['reason']);
    }

    private function makeVisitingController(Carbon $visitingSince): Account
    {
        $account = Account::factory()->create();
        $account->addState(State::findByCode('VISITING'));

        DB::table('mship_account_state')
            ->where('account_id', $account->id)
            ->update(['start_at' => $visitingSince]);

        return $account->fresh();
    }

    private function recordRosterRemoval(Account $account, Carbon $removedAt): void
    {
        $history = RosterHistory::create([
            'account_id' => $account->id,
            'original_created_at' => $removedAt,
            'original_updated_at' => $removedAt,
            'removed_by' => null,
            'roster_update_id' => 1,
        ]);

        $history->created_at = $removedAt;
        $history->saveQuietly();
    }
}
