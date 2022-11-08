<?php

namespace Tests\Unit\Training\WaitingList;

use App\Models\Mship\Account;
use App\Models\NetworkData\Atc;
use App\Models\Training\WaitingList;
use App\Models\Training\WaitingList\WaitingListStatus;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class WaitingListAccountTest extends TestCase
{
    use DatabaseTransactions, WaitingListTestHelper;

    private $waitingList;

    protected function setUp(): void
    {
        parent::setUp();

        $this->waitingList = $this->createList();
    }

    /** @test * */
    public function itIsNotMarkedForRemovalOnCreation()
    {
        $account = factory(Account::class)->create();

        $waitingListAccount = $this->waitingList->addToWaitingList($account, $this->privacc);

        $this->assertNull($this->waitingList->accounts->first()->pivot->pending_removal);
    }

    /** @test * */
    public function itCanBeMarkedForRemoval()
    {
        $account = factory(Account::class)->create();

        $waitingListAccount = $this->waitingList->addToWaitingList($account, $this->privacc);

        $testRemovalDate    = Carbon::parse("next week");
        
        $this->waitingList->accounts->first()->pivot->addPendingRemoval($testRemovalDate);

        $this->assertDatabaseHas('training_waiting_list_account_pending_removal', [
                'removal_date' => $testRemovalDate->toDateTimeString()
        ]);
    }

    /** @test * */
    public function itCanHaveAStatusAssociatedWithIt()
    {
        $status = factory(WaitingListStatus::class)->create();

        $account = factory(Account::class)->create();

        $waitingListAccount = $this->waitingList->addToWaitingList($account, $this->privacc);

        $this->waitingList->accounts->first()->pivot->addStatus($status);

        $this->assertDatabaseHas('training_waiting_list_account_status', [
            'status_id' => $status->id,
            'start_at' => now()->toDateTimeString(),
        ]);
    }

    /** @test * */
    public function itRemovedOldStatusesOnAdd()
    {
        $status = factory(WaitingListStatus::class)->create();

        $secondStatus = factory(WaitingListStatus::class)->create();

        $account = factory(Account::class)->create();

        $this->waitingList->addToWaitingList($account, $this->privacc);

        $this->waitingList->accounts->find($account->id)->pivot->addStatus($status);

        // adding a new status should mean the first status is marked as ended.
        $this->waitingList->fresh()->accounts->find($account->id)->pivot->addStatus($secondStatus);

        $this->assertDatabaseHas('training_waiting_list_account_status', [
            'status_id' => $status->id,
            'end_at' => now(),
        ]);

        $this->assertDatabaseHas('training_waiting_list_account_status', [
            'status_id' => $secondStatus->id,
            'start_at' => now(),
            'end_at' => null,
        ]);
    }

    /** @test * */
    public function itChecksFor12HourRequirement()
    {
        $account = factory(Account::class)->create();

        factory(Atc::class)->create(
            [
                'account_id' => $account->id,
                'minutes_online' => 721,
                'disconnected_at' => now(),
            ]);

        $this->waitingList->addToWaitingList($account, $this->privacc);

        $this->assertTrue($this->waitingList->accounts->find($account->id)->pivot->atcHourCheck());
    }

    /** @test */
    public function itDetectsWhen12HourRequirementHaveNotBeenMet()
    {
        $account = factory(Account::class)->create();

        factory(Atc::class)->create(
            [
                'account_id' => $account->id,
                'minutes_online' => 20,
                'disconnected_at' => now(),
            ]);

        $this->waitingList->addToWaitingList($account, $this->privacc);

        $this->assertFalse($this->waitingList->accounts->find($account->id)->pivot->atcHourCheck());
    }

    /** @test */
    public function itChecksForMultipleSessionsIn12HourRequirement()
    {
        $account = factory(Account::class)->create();

        factory(Atc::class, 12)->create(
            [
                'account_id' => $account->id,
                'minutes_online' => 60,
                'disconnected_at' => now(),
            ]);

        $this->waitingList->addToWaitingList($account, $this->privacc);

        $this->assertTrue($this->waitingList->accounts->find($account->id)->pivot->atcHourCheck());
    }

    /** @test */
    public function itDisregardsNonUKControllingSessionsForHourCheck()
    {
        $account = factory(Account::class)->create();

        // 12 sessions of an hour each to satisfy the requirement, but with non-uk callsign
        factory(Atc::class, 12)->create(
            [
                'account_id' => $account->id,
                'minutes_online' => 60,
                'disconnected_at' => now(), 'callsign' => 'LFPG_APP',
            ]);

        $this->waitingList->addToWaitingList($account, $this->privacc);

        $this->assertFalse($this->waitingList->accounts->find($account->id)->pivot->atcHourCheck());
    }

    /** @test */
    public function itDisregardsSessionsGreaterThan3MonthsAgo()
    {
        $account = factory(Account::class)->create();

        // 12 sessions of an hour satisfy the hour requirement, but not the date range
        // subtracting 3 months and a day satisfies a boundary condition
        factory(Atc::class, 12)
            ->create(
                [
                    'account_id' => $account->id, 'minutes_online' => 60,
                    'disconnected_at' => now()->subMonth(3)->subDay(1),
                ]);

        $this->waitingList->addToWaitingList($account, $this->privacc);

        $this->assertFalse($this->waitingList->accounts->find($account->id)->pivot->atcHourCheck());
    }

    /** @test */
    public function itHandlesExactly3MonthsAgoForAtcHourCheckCorrectly()
    {
        $account = factory(Account::class)->create();

        // 12 sessions of an hour which occurred within the 3 month range
        factory(Atc::class, 12)
            ->create(
                [
                    'account_id' => $account->id, 'minutes_online' => 60,
                    'disconnected_at' => now()->subMonth(3),
                ]);

        $this->waitingList->addToWaitingList($account, $this->privacc);

        $this->assertTrue($this->waitingList->accounts->find($account->id)->pivot->atcHourCheck());
    }

    /** @test */
    public function itHandlesJustShortOfThe12HourRequirement()
    {
        $account = factory(Account::class)->create();

        // 11 sessions of an hour which occurred within the 3 month range
        factory(Atc::class, 11)
            ->create(
                [
                    'account_id' => $account->id, 'minutes_online' => 60,
                    'disconnected_at' => now(),
                ]);

        // last session brings the total to 11 hours 59 minutes of controlling time.
        factory(Atc::class, 1)
            ->create(
                [
                    'account_id' => $account->id, 'minutes_online' => 59,
                    'disconnected_at' => now(),
                ]);

        $this->waitingList->addToWaitingList($account, $this->privacc);

        $this->assertFalse($this->waitingList->accounts->find($account->id)->pivot->atcHourCheck());
    }

    /** @test */
    public function itCanHaveNotesAdded()
    {
        $account = factory(Account::class)->create();

        $this->waitingList->addToWaitingList($account, $this->privacc);

        // grab the pivot model
        $waitingListAccount = $this->waitingList->accounts->find($account->id)->pivot;

        $waitingListAccount->notes = 'This is a note';

        $this->assertEquals('This is a note', $waitingListAccount->notes);
    }

    /** @test */
    public function itCachesHourRequirementFlagWhenNotMetAndNoKeyExists()
    {
        $ttlDay = 86400;
        $account = factory(Account::class)->create();

        $this->waitingList->addToWaitingList($account, $this->privacc);

        // grab the pivot model
        $waitingListAccount = $this->waitingList->accounts->find($account->id)->pivot;

        Cache::shouldReceive('has')
            ->once()
            ->andReturn(false);

        Cache::shouldReceive('put')
            ->once()
            ->with("waiting-list-account:{$waitingListAccount->id}:recentAtcMins", null, $ttlDay);

        $this->assertFalse($waitingListAccount->atcHourCheck());
    }

    /** @test */
    public function itCachesHourRequirementWheMetAndKeyDoesntExist()
    {
        $ttlDay = 86400;
        $account = factory(Account::class)->create();

        $this->waitingList->addToWaitingList($account, $this->privacc);

        factory(Atc::class)->create(
            [
                'account_id' => $account->id,
                'minutes_online' => 721,
                'disconnected_at' => now(),
            ]);

        // grab the pivot model
        $waitingListAccount = $this->waitingList->accounts->find($account->id)->pivot;

        Cache::shouldReceive('has')
            ->once()
            ->andReturn(false);

        Cache::shouldReceive('put')
            ->once()
            ->with("waiting-list-account:{$waitingListAccount->id}:recentAtcMins", 721, $ttlDay);

        $this->assertTrue($waitingListAccount->atcHourCheck());
    }

    /** @test */
    public function itShouldReturnTheValueIfExists()
    {
        $account = factory(Account::class)->create();
        $this->waitingList->addToWaitingList($account, $this->privacc);

        $waitingListAccount = $this->waitingList->accounts->find($account->id)->pivot;

        Cache::shouldReceive('has')
            ->once()
            ->andReturn(true);

        Cache::shouldReceive('get')
            ->once()
            ->andReturn(false);

        $this->assertFalse($waitingListAccount->atcHourCheck());
    }

    /** @test */
    public function itShouldDefaultCreatedAtToNowIfNotProvided()
    {
        $account = factory(Account::class)->create();
        $this->waitingList->addToWaitingList($account, $this->privacc);

        $this->assertDatabaseHas('training_waiting_list_account', [
            'account_id' => $account->id,
            'list_id' => $this->waitingList->id,
            'created_at' => $this->knownDate,
        ]);
    }

    /** @test */
    public function itShouldSetCreatedAtToGivenDateIfProvided()
    {
        $date = Carbon::parse('2020-01-01 12:00:00');
        $account = factory(Account::class)->create();
        $this->waitingList->addToWaitingList($account, $this->privacc, $date);

        $this->assertDatabaseHas('training_waiting_list_account', [
            'account_id' => $account->id,
            'list_id' => $this->waitingList->id,
            'created_at' => $date,
        ]);
    }

    /** @test */
    public function itShouldPassHourCheckIfPilotWaitingList()
    {
        $pilotList = factory(WaitingList::class)->create(['department' => 'pilot']);
        $account = factory(Account::class)->create();

        $pilotList->addToWaitingList($account, $this->privacc);

        $this->assertTrue($pilotList->accounts()->find($account)->pivot->atcHourCheck());
    }
}
