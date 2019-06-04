<?php

namespace Tests\Unit\Training;

use App\Events\Training\AccountAddedToWaitingList;
use App\Listeners\Training\WaitingList\AssignFlags;
use App\Models\Training\WaitingList;
use App\Models\Training\WaitingListFlag;
use App\Models\Mship\Account;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class WaitingListFlagTest extends TestCase
{
    use DatabaseTransactions;

    /** @var WaitingListFlag */
    private $flag;
    /** @var WaitingList */
    private $waitingList;

    protected function setUp()
    {
        parent::setUp();

        $this->flag = factory(WaitingListFlag::class)->create();

        $this->waitingList = factory(WaitingList::class)->create();
        $this->waitingList->addFlag($this->flag);
        $this->waitingList->addToWaitingList($this->privacc, $this->privacc);

        Event::fake();
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
        $account = factory(Account::class)->create();

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
        $account = factory(Account::class)->create();
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
