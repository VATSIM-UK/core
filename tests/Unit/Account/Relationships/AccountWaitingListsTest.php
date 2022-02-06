<?php

namespace Tests\Unit\Account\Relationships;

use App\Models\Training\WaitingList;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AccountWaitingListsTests extends TestCase
{
    use DatabaseTransactions;

    private $oldWaitingList, $currentWaitingList;

    public function setUp(): void
    {
        parent::setUp();

        $this->oldWaitingList = factory(WaitingList::class)->create();
        $this->currentWaitingList = factory(WaitingList::class)->create();

        $this->oldWaitingList->addToWaitingList($this->user, $this->privacc);
        $this->currentWaitingList->addToWaitingList($this->user, $this->privacc);

        $this->oldWaitingList->removeFromWaitingList($this->user);
    }

    /** @test */
    public function itCanGetAllWaitingLists()
    {
        $this->assertCount(2, $this->user->waitingLists);
        $this->assertContains($this->oldWaitingList->id, $this->user->waitingLists->pluck('id'));
        $this->assertContains($this->currentWaitingList->id, $this->user->waitingLists->pluck('id'));
    }

    /** @test */
    public function itCanGetAllCurrentWaitingLists()
    {
        $this->assertCount(1, $this->user->currentWaitingLists);
        $this->assertContains($this->currentWaitingList->id, $this->user->currentWaitingLists->pluck('id'));
    }
}
