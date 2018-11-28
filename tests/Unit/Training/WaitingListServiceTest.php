<?php

namespace Tests\Unit\Training;

use App\Models\Mship\Account;
use App\Models\Training\WaitingList;
use App\Services\Training\AddToWaitingList;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class WaitingListServiceTest extends TestCase
{
    use DatabaseTransactions;

    /** @var \App\Models\Training\WaitingList */
    private $waitingList;

    protected function setUp()
    {
        parent::setUp();

        $this->waitingList = factory(WaitingList::class)->create();
    }

    /** @test **/
    public function itAddsAccountToWaitingListWithDefaultStatus()
    {
        $account = factory(Account::class)->create();

        handleService(new AddToWaitingList($this->waitingList, $account->first(), $this->privacc));

        $this->assertEquals(1, $this->waitingList->fresh()->accounts->count());

        $this->assertEquals(1, $this->waitingList->fresh()->accounts->first()->pivot->status->count());
    }

    /** @test **/
    public function itHandlesAStudentBeingAddedAfterRemoval()
    {
        $account = factory(Account::class)->create();

        handleService(new AddToWaitingList($this->waitingList, $account->fresh(), $this->privacc));

        $this->assertEquals(1, $this->waitingList->accounts()->count());

        $this->waitingList->removeFromWaitingList($account->fresh());

        $this->assertEquals(0, $this->waitingList->fresh()->accounts()->count());

        handleService(new AddToWaitingList($this->waitingList, $account, $this->privacc));

        $this->assertEquals(1, $this->waitingList->fresh()->accounts()->count());
    }
}
