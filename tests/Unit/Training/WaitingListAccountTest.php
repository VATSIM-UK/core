<?php

namespace Tests\Unit\Training;

use App\Models\Mship\Account;
use App\Models\NetworkData\Atc;
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

        $this->waitingList->accounts->first()->pivot->addStatus($status);

        $this->waitingList->accounts->first()->pivot->first()->addStatus($secondStatus);

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

        $data = factory(Atc::class)->create(['account_id' => $account->id, 'minutes_online' => 721, 'disconnected_at' => now()]);

        $this->waitingList->addToWaitingList($account, $this->privacc);

        $this->assertTrue($this->waitingList->accounts->find($account->id)->pivot->atcHourCheck());
    }
}
