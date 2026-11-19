<?php

declare(strict_types=1);

namespace Tests\Unit\Command;

use App\Models\Atc\Position;
use App\Models\Atc\PositionGroup;
use App\Models\Mship\Account;
use App\Models\Mship\Account\Endorsement;
use App\Models\Mship\Qualification;
use App\Models\Mship\State;
use App\Models\NetworkData\Atc;
use App\Models\Roster;
use App\Models\Training\WaitingList;
use App\Notifications\Roster\RemovedFromRoster;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class UpdateRosterCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Http::fake();
    }

    #[Test]
    public function it_keeps_a_visitor_who_only_holds_the_shanwick_endorsement_on_the_roster(): void
    {
        $account = $this->makeController('VISITING');
        $this->giveGroupEndorsement($account, PositionGroup::SHANWICK_POSITION_GROUP);

        $this->runRosterUpdate();

        $this->assertDatabaseHas('roster', ['account_id' => $account->id]);
        $this->assertDatabaseMissing('roster_history', ['account_id' => $account->id]);
        Notification::assertNotSentTo($account, RemovedFromRoster::class);
    }

    #[Test]
    public function it_keeps_a_visitor_endorsed_on_a_position_in_the_shanwick_group(): void
    {
        $account = $this->makeController('VISITING');

        $positionGroup = PositionGroup::factory()->create(['name' => PositionGroup::SHANWICK_POSITION_GROUP]);
        $position = Position::factory()->create();
        $positionGroup->positions()->attach($position->id);

        Endorsement::createQuietly([
            'account_id' => $account->id,
            'endorsable_type' => Position::class,
            'endorsable_id' => $position->id,
        ]);

        $this->runRosterUpdate();

        $this->assertDatabaseHas('roster', ['account_id' => $account->id]);
    }

    #[Test]
    public function it_removes_a_visitor_who_holds_endorsements_beyond_shanwick(): void
    {
        $account = $this->makeController('VISITING');
        $this->giveGroupEndorsement($account, PositionGroup::SHANWICK_POSITION_GROUP);
        $this->giveGroupEndorsement($account, 'Heathrow');

        $this->runRosterUpdate();

        $this->assertDatabaseMissing('roster', ['account_id' => $account->id]);
        $this->assertDatabaseHas('roster_history', ['account_id' => $account->id]);
        Notification::assertSentTo($account, RemovedFromRoster::class);
    }

    #[Test]
    public function it_removes_a_visitor_without_any_endorsements(): void
    {
        $account = $this->makeController('VISITING');

        $this->runRosterUpdate();

        $this->assertDatabaseMissing('roster', ['account_id' => $account->id]);
        $this->assertDatabaseHas('roster_history', ['account_id' => $account->id]);
    }

    #[Test]
    public function it_removes_a_home_member_who_only_holds_the_shanwick_endorsement(): void
    {
        $account = $this->makeController('DIVISION');
        $this->giveGroupEndorsement($account, PositionGroup::SHANWICK_POSITION_GROUP);

        $this->runRosterUpdate();

        $this->assertDatabaseMissing('roster', ['account_id' => $account->id]);
        $this->assertDatabaseHas('roster_history', ['account_id' => $account->id]);
    }

    #[Test]
    public function it_removes_a_non_shanwick_visitor_from_a_roster_membership_waiting_list(): void
    {
        $account = $this->makeController('VISITING');

        $waitingList = WaitingList::factory()->create([
            'department' => WaitingList::ATC_DEPARTMENT,
            'requires_roster_membership' => true,
        ]);

        $waitingListAccountId = DB::table('training_waiting_list_account')->insertGetId([
            'list_id' => $waitingList->id,
            'account_id' => $account->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->runRosterUpdate();

        $this->assertSoftDeleted('training_waiting_list_account', ['id' => $waitingListAccountId]);
    }

    #[Test]
    public function it_logs_a_warning_when_the_shanwick_position_group_is_missing(): void
    {
        Log::spy();

        $account = $this->makeController('VISITING');

        $this->runRosterUpdate();

        Log::shouldHaveReceived('warning')
            ->withArgs(fn (string $message) => str_contains($message, 'position group not found'))
            ->once();

        // Without the exemption the visitor falls back to the normal removal rules.
        $this->assertDatabaseMissing('roster', ['account_id' => $account->id]);
    }

    #[Test]
    public function it_keeps_a_shanwick_only_visitor_on_a_roster_membership_waiting_list(): void
    {
        $account = $this->makeController('VISITING');
        $this->giveGroupEndorsement($account, PositionGroup::SHANWICK_POSITION_GROUP);

        $waitingList = WaitingList::factory()->create([
            'department' => WaitingList::ATC_DEPARTMENT,
            'requires_roster_membership' => true,
        ]);

        $waitingListAccountId = DB::table('training_waiting_list_account')->insertGetId([
            'list_id' => $waitingList->id,
            'account_id' => $account->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->runRosterUpdate();

        $this->assertDatabaseHas('training_waiting_list_account', [
            'id' => $waitingListAccountId,
            'deleted_at' => null,
        ]);
    }

    #[Test]
    public function it_does_not_add_an_eligible_controller_who_is_not_already_on_the_roster(): void
    {
        $account = Account::factory()->create();
        $account->addState(State::findByCode('DIVISION'));
        $this->giveControllingHours($account);

        $this->runRosterUpdate();

        $this->assertDatabaseMissing('roster', ['account_id' => $account->id]);
    }

    #[Test]
    public function it_does_not_re_add_a_controller_who_was_removed_from_the_roster(): void
    {
        $account = $this->makeController('DIVISION');
        $this->giveControllingHours($account);

        Roster::withoutGlobalScopes()->where('account_id', $account->id)->first()->remove();

        $this->assertDatabaseMissing('roster', ['account_id' => $account->id]);

        $this->runRosterUpdate();

        $this->assertDatabaseMissing('roster', ['account_id' => $account->id]);
    }

    #[Test]
    public function it_keeps_a_newly_qualified_s1_who_has_not_met_the_hours_requirement(): void
    {
        $account = $this->makeController('DIVISION');
        $this->giveS1Qualification($account, now()->subMonth());

        $this->runRosterUpdate();

        $this->assertDatabaseHas('roster', ['account_id' => $account->id]);
        Notification::assertNotSentTo($account, RemovedFromRoster::class);
    }

    #[Test]
    public function it_removes_an_s1_who_has_not_met_the_hours_requirement_after_their_first_full_quarter(): void
    {
        $account = $this->makeController('DIVISION');
        $this->giveS1Qualification($account, now()->subMonths(4));

        $this->runRosterUpdate();

        $this->assertDatabaseMissing('roster', ['account_id' => $account->id]);
        Notification::assertSentTo($account, RemovedFromRoster::class);
    }

    #[Test]
    public function it_removes_an_ineligible_account_from_a_roster_membership_waiting_list_even_if_it_meets_the_hours_requirement(): void
    {
        // A manually removed controller who still meets the hours requirement is not on the roster, so they should no longer count as a roster member for waiting lists.
        $account = Account::factory()->create();
        $account->addState(State::findByCode('DIVISION'));
        $this->giveControllingHours($account);

        $waitingList = WaitingList::factory()->create([
            'department' => WaitingList::ATC_DEPARTMENT,
            'requires_roster_membership' => true,
        ]);

        $waitingListAccountId = DB::table('training_waiting_list_account')->insertGetId([
            'list_id' => $waitingList->id,
            'account_id' => $account->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->runRosterUpdate();

        $this->assertSoftDeleted('training_waiting_list_account', ['id' => $waitingListAccountId]);
    }

    private function makeController(string $state): Account
    {
        $account = Account::factory()->create();
        $account->addState(State::findByCode($state));

        Roster::create(['account_id' => $account->id]);

        return $account->fresh();
    }

    private function giveS1Qualification(Account $account, CarbonInterface $createdAt): void
    {
        $qualification = Qualification::code('S1')->firstOrFail();

        $account->qualifications()->attach($qualification->id);

        DB::table('mship_account_qualification')
            ->where('account_id', $account->id)
            ->where('qualification_id', $qualification->id)
            ->update(['created_at' => $createdAt]);
    }

    private function giveControllingHours(Account $account): void
    {
        factory(Atc::class)->states('offline')->create([
            'account_id' => $account->id,
            'callsign' => 'EGKK_TWR',
            'connected_at' => now()->subHours(4),
            'disconnected_at' => now()->subHours(1),
            'minutes_online' => 240,
        ]);
    }

    private function giveGroupEndorsement(Account $account, string $positionGroupName): Endorsement
    {
        return Endorsement::createQuietly([
            'account_id' => $account->id,
            'endorsable_type' => PositionGroup::class,
            'endorsable_id' => PositionGroup::factory()->create(['name' => $positionGroupName])->id,
        ]);
    }

    private function runRosterUpdate(): void
    {
        Artisan::call('roster:update', [
            'fromDate' => now()->subMonths(3)->toDateString(),
            'toDate' => now()->toDateString(),
        ]);
    }
}
