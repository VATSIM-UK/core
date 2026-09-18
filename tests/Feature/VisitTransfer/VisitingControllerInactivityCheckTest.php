<?php

namespace Tests\Feature\VisitTransfer;

use App\Models\Mship\Account;
use App\Models\Mship\State;
use App\Models\Roster;
use App\Models\RosterHistory;
use App\Notifications\VisitTransfer\VisitingStatusRevoked;
use App\Services\VisitTransfer\VisitingControllerInactivity;
use Carbon\Carbon;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class VisitingControllerInactivityCheckTest extends TestCase
{
    #[Test]
    public function it_removes_the_visiting_rights_of_an_eligible_controller(): void
    {
        $account = $this->makeVisitingController(now()->subYear());
        $this->recordRosterRemoval($account, now()->subMonths(7));

        Artisan::call('visit-transfer:check-inactivity');

        $this->assertFalse($account->fresh()->hasState('VISITING'));
        $this->assertDatabaseHas('visiting_removals', [
            'account_id' => $account->id,
            'reason' => VisitingControllerInactivity::REASON_SIX_MONTHS,
        ]);
        Notification::assertSentTo($account, VisitingStatusRevoked::class);
    }

    #[Test]
    public function it_removes_a_controller_inactive_twice_and_clears_their_roster_entry(): void
    {
        $account = $this->makeVisitingController(now()->subYears(2));
        Roster::create(['account_id' => $account->id]);

        $this->recordRosterRemoval($account, now()->subMonths(13));
        $this->recordRosterRemoval($account, now()->subMonths(2));

        Artisan::call('visit-transfer:check-inactivity');

        $this->assertFalse($account->fresh()->hasState('VISITING'));
        $this->assertDatabaseMissing('roster', ['account_id' => $account->id]);
        $this->assertDatabaseHas('visiting_removals', [
            'account_id' => $account->id,
            'reason' => VisitingControllerInactivity::REASON_TWICE_IN_TWO_YEARS,
        ]);
    }

    #[Test]
    public function it_leaves_ineligible_controllers_alone(): void
    {
        $account = $this->makeVisitingController(now()->subYear());
        $this->recordRosterRemoval($account, now()->subMonths(2));

        Artisan::call('visit-transfer:check-inactivity');

        $this->assertTrue($account->fresh()->hasState('VISITING'));
        $this->assertDatabaseMissing('visiting_removals', ['account_id' => $account->id]);
        Notification::assertNotSentTo($account, VisitingStatusRevoked::class);
    }

    #[Test]
    public function it_does_not_touch_accounts_without_a_visiting_state(): void
    {
        $account = Account::factory()->create();
        $account->addState(State::findByCode('DIVISION'));
        $this->recordRosterRemoval($account, now()->subMonths(7));

        Artisan::call('visit-transfer:check-inactivity');

        $this->assertDatabaseMissing('visiting_removals', ['account_id' => $account->id]);
        Notification::assertNotSentTo($account, VisitingStatusRevoked::class);
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
