<?php

namespace Tests\Unit\Training\WaitingList;

use App\Models\Atc\PositionGroup;
use App\Models\Mship\Account;
use App\Models\Training\WaitingList;
use App\Models\Training\WaitingList\WaitingListAccount;
use App\Models\Training\WaitingList\WaitingListFlag;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WaitingListFlagTest extends TestCase
{
    use DatabaseTransactions;

    private WaitingListFlag $flag;

    private WaitingList $waitingList;

    private WaitingListAccount $waitingListAccount;

    private PositionGroup $positionGroup;

    protected function setUp(): void
    {
        parent::setUp();

        $this->flag = factory(WaitingListFlag::class)->create();

        $this->actingAs($this->privacc);

        $this->waitingList = WaitingList::factory()->create();
        $this->waitingList->addFlag($this->flag);
        $this->waitingListAccount = $this->waitingList->addToWaitingList($this->privacc, $this->privacc);

        $this->positionGroup = factory(PositionGroup::class)->create();

    }

    #[Test]
    public function it_can_be_deleted()
    {
        $this->flag->delete();
        // tests the flag has been deleted
        $this->assertFalse($this->waitingList->flags()->exists());
        // tests that the data surrounding tha assignment of flags have been deleted on the pivot model.
        $this->assertFalse($this->waitingListAccount->flags()->exists());
    }

    #[Test]
    public function it_can_be_marked()
    {
        $waitingListAccount = $this->waitingListAccount;
        $waitingListAccount->addFlag($this->flag);

        $waitingListAccount->fresh()->markFlag($this->flag);

        // gets the pivot and finds the marked value
        $this->assertTrue($waitingListAccount->flags()->first()->pivot->value);
    }

    #[Test]
    public function it_can_be_un_marked()
    {
        $waitingListAccount = $this->waitingListAccount;
        $waitingListAccount->addFlag($this->flag);

        $waitingListAccount->fresh()->unMarkFlag($this->flag);

        // gets the pivot and finds the marked value
        $this->assertFalse($waitingListAccount->flags()->first()->pivot->value);
    }

    #[Test]
    public function it_cant_be_un_marked_when_already_un_marked()
    {
        $waitingListAccount = $this->waitingListAccount;
        $waitingListAccount->addFlag($this->flag);

        $waitingListAccount->fresh()->unMarkFlag($this->flag);

        // gets the pivot and finds the marked value
        $this->assertFalse($waitingListAccount->flags()->first()->pivot->value);
    }

    #[Test]
    public function it_assigns_default_flags_on_adding_account_to_list()
    {
        $account = Account::factory()->create();

        $waitingListAccount = $this->waitingList->addToWaitingList($account, $this->privacc);

        // checks that flags have been assigned.
        $this->assertTrue($waitingListAccount->flags()->exists());
        // flag in test has a default value of true.
        $this->assertTrue($waitingListAccount->flags()->find($this->flag->id)->pivot->value);
    }

    #[Test]
    public function it_is_propagated_to_existing_accounts_when_a_flag_is_added()
    {
        $account = Account::factory()->create();
        // null list represents a flag which hasn't yet been assigned to list.
        // Normal flow wouldn't have this, but needs to emulate the action
        $flag = factory(WaitingListFlag::class)->create(['list_id' => null]);

        $this->waitingList->addToWaitingList($account, $this->privacc);
        $this->waitingList->addFlag($flag);

        // assert that the flag which has been added is related to all the accounts which exists in the waiting list.
        $this->assertTrue($this->waitingList->waitingListAccounts()->each(function (WaitingListAccount $waitingListAccount) use ($flag) {
            $this->assertTrue($waitingListAccount->flags->contains($flag));
        }));
    }
}
