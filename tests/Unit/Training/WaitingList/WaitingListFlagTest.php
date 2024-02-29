<?php

namespace Tests\Unit\Training\WaitingList;

use App\Events\NetworkData\AtcSessionEnded;
use App\Events\Training\AccountAddedToWaitingList;
use App\Listeners\Training\WaitingList\AssignFlags;
use App\Models\Atc\PositionGroup;
use App\Models\Atc\PositionGroupCondition;
use App\Models\Mship\Account;
use App\Models\NetworkData\Atc;
use App\Models\Training\WaitingList;
use App\Models\Training\WaitingList\WaitingListFlag;
use App\Models\Training\WaitingList\WaitingListStatus;
use App\Services\Training\AddToWaitingList;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class WaitingListFlagTest extends TestCase
{
    use DatabaseTransactions;

    /** @var WaitingListFlag */
    private $flag;

    /** @var WaitingList */
    private $waitingList;

    /** @var PositionGroup */
    private $positionGroup;

    protected function setUp(): void
    {
        parent::setUp();

        $this->flag = factory(WaitingListFlag::class)->create();

        $this->actingAs($this->privacc);

        $this->waitingList = factory(WaitingList::class)->create();
        $this->waitingList->addFlag($this->flag);
        $this->waitingList->addToWaitingList($this->privacc, $this->privacc);

        $this->positionGroup = factory(PositionGroup::class)->create();

    }

    /** @test */
    public function itCanBeDeleted()
    {
        $this->flag->delete();
        // tests the flag has been deleted
        $this->assertFalse($this->waitingList->flags()->exists());
        // tests that the data surrounding tha assignment of flags have been deleted on the pivot model.
        $this->assertFalse($this->waitingList->accounts()->first()->pivot->flags()->exists());
    }

    /** @test */
    public function itCanBeMarked()
    {
        $waitingListAccount = $this->waitingList->accounts()->first()->pivot;
        $waitingListAccount->addFlag($this->flag);

        $waitingListAccount->fresh()->markFlag($this->flag);

        // gets the pivot and finds the marked value
        $this->assertTrue($waitingListAccount->flags()->first()->pivot->value);
    }

    /** @test */
    public function itCanBeUnMarked()
    {
        $waitingListAccount = $this->waitingList->accounts()->first()->pivot;
        $waitingListAccount->addFlag($this->flag);

        $waitingListAccount->fresh()->unMarkFlag($this->flag);

        // gets the pivot and finds the marked value
        $this->assertFalse($waitingListAccount->flags()->first()->pivot->value);
    }

    /** @test */
    public function itCantBeUnMarkedWhenAlreadyUnMarked()
    {
        $waitingListAccount = $this->waitingList->accounts()->first()->pivot;
        $waitingListAccount->addFlag($this->flag);

        $waitingListAccount->fresh()->unMarkFlag($this->flag);

        // gets the pivot and finds the marked value
        $this->assertFalse($waitingListAccount->flags()->first()->pivot->value);
    }

    /** @test */
    public function itAssignsDefaultFlagsOnAddingAccountToList()
    {
        $account = Account::factory()->create();

        $this->waitingList->addToWaitingList($account, $this->privacc);

        $listener = app()->make(AssignFlags::class);
        $event = \Mockery::mock(AccountAddedToWaitingList::class, [$account, $this->waitingList->fresh(), $this->privacc]);
        $listener->handle($event);

        $waitingListAccount = $this->waitingList->fresh()->accounts()->find($account->id)->pivot;

        // checks that flags have been assigned.
        $this->assertTrue($waitingListAccount->flags()->exists());
        // flag in test has a default value of true.
        $this->assertTrue($waitingListAccount->flags()->find($this->flag->id)->pivot->value);
    }

    /** @test */
    public function itIsPropagatedToExistingAccountsWhenAFlagIsAdded()
    {
        $account = Account::factory()->create();
        // null list represents a flag which hasn't yet been assigned to list.
        // Normal flow wouldn't have this, but needs to emulate the action
        $flag = factory(WaitingListFlag::class)->create(['list_id' => null]);

        $this->waitingList->addToWaitingList($account, $this->privacc);
        $this->waitingList->addFlag($flag);

        // assert that the flag which has been added is related to all the accounts which exists in the waiting list.
        $this->assertTrue($this->waitingList->accounts()->each(function ($account) use ($flag) {
            $this->assertTrue($account->pivot->flags->contains($flag));
        }));
    }
}
