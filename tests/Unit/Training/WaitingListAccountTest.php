<?php

namespace Tests\Unit\Training;

use App\Models\Mship\Account;
use App\Models\Training\WaitingListStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WaitingListAccountTest extends TestCase
{
    use RefreshDatabase, WaitingListTestHelper;

    private $waitingList;
    private $staffAccount;

    protected function setUp()
    {
        parent::setUp();

        $this->waitingList = $this->createList();

        $this->staffAccount = $this->createAdminAccount();
    }

    /** @test **/
    public function itCanHaveAStatusAssociatedWithIt()
    {
        $status = factory(WaitingListStatus::class)->create();

        $account = factory(Account::class)->create();

        $waitingListAccount = $this->waitingList->addToWaitingList($account, $this->staffAccount);

        $this->waitingList->accounts->first()->pivot->addStatus($status);
w
        $this->assertDatabaseHas('training_waiting_list_account_status', [
            'status_id' => $status->id,
            'start_at' => now(),
        ]);
    }

    /** @test **/
    public function itRemovedOldStatusesOnAdd()
    {
        $status = factory(WaitingListStatus::class)->create();

        $secondStatus = factory(WaitingListStatus::class)->create();

        $account = factory(Account::class)->create();

        $this->waitingList->addToWaitingList($account, $this->staffAccount);

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
}
