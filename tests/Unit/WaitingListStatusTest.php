<?php

namespace Tests\Unit;

use App\Models\Mship\Account;
use App\Models\Training\WaitingList;
use App\Models\Training\WaitingListStatus;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class WaitingListStatusTest extends TestCase
{
    use DatabaseTransactions;

    private $waitingListStatus;

    public function setUp()
    {
        parent::setUp();

        $this->waitingListStatus = factory(WaitingListStatus::class)->create();
    }

    /** @test * */
    public function itDefaultsToActive()
    {
        $this->assertEquals(1, $this->waitingListStatus->default()->id);
    }

    /** @test * */
    public function itAssignsAStatusOnAttach()
    {
        $waitingList = factory(WaitingList::class)->create();

        $account = factory(Account::class)->create();

        $waitingList->addToWaitingList($account);

        $this->assertDatabaseHas('training_waiting_list_account',
            ['list_id' => $waitingList->id, 'status_id' => $this->waitingListStatus->id]);
    }
}
