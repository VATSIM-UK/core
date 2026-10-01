<?php

namespace Tests\Unit\Jobs\VisitTransfer;

use App\Jobs\VisitTransfer\RemoveIneligibleVisitors;
use App\Models\Atc\PositionGroup;
use App\Models\Mship\Account;
use App\Models\Mship\Account\Endorsement;
use App\Models\Mship\State;
use App\Models\Roster;
use App\Models\RosterHistory;
use App\Models\VisitTransfer\Application;
use App\Notifications\VisitTransfer\VisitingStatusRevoked;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RemoveIneligibleVisitorsTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        DB::table('mship_account_state')
            ->where('state_id', State::findByCode('VISITING')->id)
            ->whereNull('end_at')
            ->update(['end_at' => now()]);
    }

    #[Test]
    public function it_removes_a_visitor_without_any_application(): void
    {
        $account = $this->makeVisitor(null);

        $this->assertSame(1, (new RemoveIneligibleVisitors)->handle());
        $this->assertFalse($account->fresh()->hasState('VISITING'));
        $this->assertRemovalNoted($account, RemoveIneligibleVisitors::REASON_NO_APPLICATION);
    }

    #[Test]
    public function it_removes_a_visitor_whose_application_was_cancelled(): void
    {
        $account = $this->makeVisitor(Application::STATUS_CANCELLED);

        $this->assertSame(1, (new RemoveIneligibleVisitors)->handle());
        $this->assertFalse($account->fresh()->hasState('VISITING'));
        $this->assertRemovalNoted($account, RemoveIneligibleVisitors::REASON_NO_APPLICATION);
    }

    #[Test]
    public function it_keeps_a_visitor_with_an_accepted_application(): void
    {
        $account = $this->makeVisitor(Application::STATUS_ACCEPTED, now()->subYear());
        $this->giveEndorsement($account, 'Heathrow');
        $this->recordRosterRemoval($account, now()->subMonths(7));

        $this->assertSame(0, (new RemoveIneligibleVisitors)->handle());
        $this->assertTrue($account->fresh()->hasState('VISITING'));
        $this->assertDatabaseMissing('mship_account_note', ['account_id' => $account->id]);
    }

    #[Test]
    public function it_keeps_a_completed_visitor_who_is_on_the_roster(): void
    {
        $account = $this->makeVisitor(Application::STATUS_COMPLETED, now()->subYear());
        Roster::create(['account_id' => $account->id]);
        $this->recordRosterRemoval($account, now()->subMonths(7));

        $this->assertSame(0, (new RemoveIneligibleVisitors)->handle());
        $this->assertTrue($account->fresh()->hasState('VISITING'));
        $this->assertDatabaseMissing('mship_account_note', ['account_id' => $account->id]);
    }

    #[Test]
    public function it_returns_a_completed_shanwick_only_visitor_to_the_roster(): void
    {
        $account = $this->makeVisitor(Application::STATUS_COMPLETED, now()->subYear());
        $this->giveEndorsement($account, RemoveIneligibleVisitors::SHANWICK_POSITION_GROUP);

        $this->assertSame(0, (new RemoveIneligibleVisitors)->handle());
        $this->assertTrue($account->fresh()->hasState('VISITING'));
        $this->assertDatabaseHas('roster', ['account_id' => $account->id]);
        $this->assertTrue($account->fresh()->onRoster());
        $this->assertDatabaseMissing('mship_account_note', ['account_id' => $account->id]);
    }

    #[Test]
    public function it_returns_a_shanwick_only_visitor_without_an_application_to_the_roster(): void
    {
        $account = $this->makeVisitor(null);
        $this->giveEndorsement($account, RemoveIneligibleVisitors::SHANWICK_POSITION_GROUP);

        $this->assertSame(0, (new RemoveIneligibleVisitors)->handle());
        $this->assertTrue($account->fresh()->hasState('VISITING'));
        $this->assertTrue($account->fresh()->onRoster());
    }

    #[Test]
    public function it_removes_a_completed_visitor_who_has_been_off_the_roster_for_six_months(): void
    {
        $account = $this->makeVisitor(Application::STATUS_COMPLETED, now()->subYear());
        $this->giveEndorsement($account, 'Heathrow');
        $this->recordRosterRemoval($account, now()->subMonths(7));

        $this->assertSame(1, (new RemoveIneligibleVisitors)->handle());
        $this->assertFalse($account->fresh()->hasState('VISITING'));
        $this->assertRemovalNoted($account, RemoveIneligibleVisitors::REASON_SIX_MONTHS);
    }

    #[Test]
    public function it_removes_a_completed_visitor_who_has_left_the_roster_twice_in_two_years(): void
    {
        $account = $this->makeVisitor(Application::STATUS_COMPLETED, now()->subYears(2));
        $this->giveEndorsement($account, 'Heathrow');
        Roster::create(['account_id' => $account->id]);
        $this->recordRosterRemoval($account, now()->subMonths(13));
        $this->recordRosterRemoval($account, now()->subMonths(2));

        $this->assertSame(1, (new RemoveIneligibleVisitors)->handle());
        $this->assertFalse($account->fresh()->hasState('VISITING'));
        $this->assertRemovalNoted($account, RemoveIneligibleVisitors::REASON_TWICE_IN_TWO_YEARS);
    }

    #[Test]
    public function it_clears_the_roster_entry_of_a_removed_visitor(): void
    {
        $account = $this->makeVisitor(Application::STATUS_COMPLETED, now()->subYears(2));
        Roster::create(['account_id' => $account->id]);
        $this->recordRosterRemoval($account, now()->subMonths(13));
        $this->recordRosterRemoval($account, now()->subMonths(2));

        $this->assertSame(1, (new RemoveIneligibleVisitors)->handle());
        $this->assertDatabaseMissing('roster', ['account_id' => $account->id]);
        $this->assertFalse($account->fresh()->onRoster());
    }

    #[Test]
    public function it_keeps_a_completed_visitor_who_recently_left_the_roster(): void
    {
        $account = $this->makeVisitor(Application::STATUS_COMPLETED, now()->subYear());
        $this->giveEndorsement($account, 'Heathrow');
        $this->recordRosterRemoval($account, now()->subMonths(2));

        $this->assertSame(0, (new RemoveIneligibleVisitors)->handle());
        $this->assertTrue($account->fresh()->hasState('VISITING'));
    }

    #[Test]
    public function it_removes_a_completed_visitor_who_has_never_been_on_the_roster(): void
    {
        $account = $this->makeVisitor(Application::STATUS_COMPLETED, now()->subYear());
        $this->giveEndorsement($account, 'Heathrow');

        $this->assertSame(1, (new RemoveIneligibleVisitors)->handle());
        $this->assertFalse($account->fresh()->hasState('VISITING'));
        $this->assertRemovalNoted($account, RemoveIneligibleVisitors::REASON_NEVER_ON_ROSTER);
    }

    #[Test]
    public function it_removes_a_pilot_visitor_who_completed_their_training(): void
    {
        $account = $this->makeVisitor(Application::STATUS_COMPLETED, now()->subYear(), 'pilot');

        $this->assertSame(1, (new RemoveIneligibleVisitors)->handle());
        $this->assertFalse($account->fresh()->hasState('VISITING'));
        $this->assertRemovalNoted($account, RemoveIneligibleVisitors::REASON_NON_ATC_APPLICATION);
    }

    #[Test]
    public function it_removes_a_completed_visitor_who_holds_endorsements_beyond_shanwick(): void
    {
        $account = $this->makeVisitor(Application::STATUS_COMPLETED, now()->subYear());
        $this->giveEndorsement($account, RemoveIneligibleVisitors::SHANWICK_POSITION_GROUP);
        $this->giveEndorsement($account, 'Heathrow');
        $this->recordRosterRemoval($account, now()->subMonths(7));

        $this->assertSame(1, (new RemoveIneligibleVisitors)->handle());
        $this->assertFalse($account->fresh()->hasState('VISITING'));
    }

    #[Test]
    public function it_does_not_record_a_visiting_removal_or_notify_the_member(): void
    {
        $account = $this->makeVisitor(Application::STATUS_CANCELLED);

        (new RemoveIneligibleVisitors)->handle();

        $this->assertDatabaseMissing('visiting_removals', ['account_id' => $account->id]);
        Notification::assertNotSentTo($account, VisitingStatusRevoked::class);
    }

    private function makeVisitor(?int $status, ?Carbon $visitingSince = null, string $team = 'atc'): Account
    {
        $account = Account::factory()->create();
        $account->addState(State::findByCode('VISITING'));

        DB::table('mship_account_state')
            ->where('account_id', $account->id)
            ->update(['start_at' => $visitingSince ?? now()]);

        if ($status !== null) {
            Application::factory()->visit($team)->create([
                'account_id' => $account->id,
                'status' => $status,
            ]);
        }

        return $account->fresh();
    }

    private function giveEndorsement(Account $account, string $positionGroupName): Endorsement
    {
        return Endorsement::createQuietly([
            'account_id' => $account->id,
            'endorsable_type' => PositionGroup::class,
            'endorsable_id' => PositionGroup::factory()->create(['name' => $positionGroupName])->id,
        ]);
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

    private function assertRemovalNoted(Account $account, string $reason): void
    {
        $this->assertDatabaseHas('mship_account_note', [
            'account_id' => $account->id,
            'content' => 'Removed as visitor for '.RemoveIneligibleVisitors::reasonText($reason).'.',
        ]);
    }
}
