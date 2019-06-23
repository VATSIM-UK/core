<?php

namespace Tests\Unit\Training;

use App\Models\Mship\Account;
use App\Models\NetworkData\Atc;
use App\Models\Training\WaitingListFlag;
use App\Models\Training\WaitingListStatus;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class WaitingListAccountTest extends TestCase
{
    use DatabaseTransactions, WaitingListTestHelper;

    private $waitingList;

    protected function setUp()
    {
        parent::setUp();

        $this->waitingList = $this->createList();
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
                'disconnected_at' => now()
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
                'disconnected_at' => now()
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
                'disconnected_at' => now()
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
                'disconnected_at' => now(), 'callsign' => 'LFPG_APP'
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
                    'disconnected_at' => now()->subMonth(3)->subDay(1)
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
                    'disconnected_at' => now()->subMonth(3)
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
                    'disconnected_at' => now()
                ]);

        // last session brings the total to 11 hours 59 minutes of controlling time.
        factory(Atc::class, 1)
            ->create(
                [
                    'account_id' => $account->id, 'minutes_online' => 59,
                    'disconnected_at' => now()
                ]);

        $this->waitingList->addToWaitingList($account, $this->privacc);

        $this->assertFalse($this->waitingList->accounts->find($account->id)->pivot->atcHourCheck());
    }
}
