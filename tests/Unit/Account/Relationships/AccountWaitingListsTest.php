<?php

namespace Tests\Unit\Account\Relationships;

use App\Models\Mship\State;
use App\Models\Training\WaitingList;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AccountWaitingListsTest extends TestCase
{
    use DatabaseTransactions;

    private WaitingList $oldWaitingList;

    private WaitingList $currentWaitingList;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs($this->privacc);

        // create as division member
        $this->user->addState(State::findByCode('DIVISION'));

        $this->oldWaitingList = factory(WaitingList::class)->create();
        $this->currentWaitingList = factory(WaitingList::class)->create();

        $this->oldWaitingList->addToWaitingList($this->user, $this->privacc);
        $this->currentWaitingList->addToWaitingList($this->user, $this->privacc);

        $this->oldWaitingList->removeFromWaitingList($this->user, new WaitingList\Removal(WaitingList\RemovalReason::Other, null, null));
    }

    /** @test */
    public function it_can_get_all_waiting_lists()
    {
        $this->assertCount(2, $this->user->waitingLists());
        $this->assertContains($this->oldWaitingList->id, $this->user->waitingLists()->pluck('id'));
        $this->assertContains($this->currentWaitingList->id, $this->user->waitingLists()->pluck('id'));
    }

    /** @test */
    public function it_can_get_all_current_waiting_lists()
    {
        $this->assertCount(1, $this->user->fresh()->currentWaitingLists());
        $this->assertContains($this->currentWaitingList->id, $this->user->fresh()->currentWaitingLists()->pluck('id'));
    }

    /** @test */
    public function it_can_handle_trashed_waiting_lists()
    {
        $trashed = factory(WaitingList::class)->create();
        $trashed->addToWaitingList($this->user, $this->privacc);
        $trashed->delete();
        $trashed->save();

        $this->assertCount(1, $this->user->fresh()->currentWaitingLists());
        $this->assertCount(2, $this->user->fresh()->waitingLists());
    }
}
