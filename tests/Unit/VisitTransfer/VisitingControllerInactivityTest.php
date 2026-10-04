<?php

namespace Tests\Unit\VisitTransfer;

use App\Models\Atc\PositionGroup;
use App\Models\Mship\Account;
use App\Models\Mship\Account\Endorsement;
use App\Models\Mship\State;
use App\Models\Roster;
use App\Models\RosterHistory;
use App\Models\VisitTransfer\Application;
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

    #[Test]
    public function it_does_not_flag_pilot_visitors(): void
    {
        $account = $this->makeVisitingController(now()->subYear(), 'pilot');
        $this->recordRosterRemoval($account, now()->subMonths(7));

        $this->assertNull($this->service->reasonFor($account));
    }

    #[Test]
    public function it_does_not_flag_visitors_with_an_open_application(): void
    {
        $openStatuses = [
            Application::STATUS_IN_PROGRESS,
            Application::STATUS_SUBMITTED,
            Application::STATUS_UNDER_REVIEW,
            Application::STATUS_ACCEPTED,
        ];

        foreach ($openStatuses as $status) {
            $account = $this->makeVisitingController(now()->subYear());
            $this->recordRosterRemoval($account, now()->subMonths(7));

            Application::factory()->visit('atc')->create([
                'account_id' => $account->id,
                'status' => $status,
            ]);

            $this->assertNull($this->service->reasonFor($account->fresh()), "An application with status {$status} should exclude the visitor from removal.");
        }
    }

    #[Test]
    public function it_flags_visitors_whose_earlier_application_was_cancelled(): void
    {
        $account = $this->makeVisitingController(now()->subYear());
        $this->recordRosterRemoval($account, now()->subMonths(7));

        Application::factory()->visit('atc')->create([
            'account_id' => $account->id,
            'status' => Application::STATUS_CANCELLED,
        ]);

        $this->assertSame(VisitingControllerInactivity::REASON_SIX_MONTHS, $this->service->reasonFor($account->fresh()));
    }

    #[Test]
    public function it_does_not_flag_visitors_who_only_hold_the_shanwick_endorsement(): void
    {
        $account = $this->makeVisitingController(now()->subYear());
        $this->recordRosterRemoval($account, now()->subMonths(7));

        $shanwick = PositionGroup::factory()->create(['name' => VisitingControllerInactivity::SHANWICK_POSITION_GROUP]);

        Endorsement::createQuietly([
            'account_id' => $account->id,
            'endorsable_type' => PositionGroup::class,
            'endorsable_id' => $shanwick->id,
        ]);

        $this->assertNull($this->service->reasonFor($account->fresh()));
    }

    #[Test]
    public function it_flags_visitors_who_hold_endorsements_beyond_shanwick(): void
    {
        $account = $this->makeVisitingController(now()->subYear());
        $this->recordRosterRemoval($account, now()->subMonths(7));

        $shanwick = PositionGroup::factory()->create(['name' => VisitingControllerInactivity::SHANWICK_POSITION_GROUP]);
        $heathrow = PositionGroup::factory()->create(['name' => 'Heathrow']);

        foreach ([$shanwick, $heathrow] as $positionGroup) {
            Endorsement::createQuietly([
                'account_id' => $account->id,
                'endorsable_type' => PositionGroup::class,
                'endorsable_id' => $positionGroup->id,
            ]);
        }

        $this->assertSame(VisitingControllerInactivity::REASON_SIX_MONTHS, $this->service->reasonFor($account->fresh()));
    }

    private function makeVisitingController(Carbon $visitingSince, string $team = 'atc'): Account
    {
        $account = Account::factory()->create();
        $account->addState(State::findByCode('VISITING'));

        DB::table('mship_account_state')
            ->where('account_id', $account->id)
            ->update(['start_at' => $visitingSince]);

        Application::factory()->visit($team)->create([
            'account_id' => $account->id,
            'status' => Application::STATUS_COMPLETED,
        ]);

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
