<?php

namespace Tests\Unit\Training\WaitingList;

use App\Events\Training\AccountAddedToWaitingList;
use App\Listeners\Training\WaitingList\AssignFlags;
use App\Models\Atc\Endorsement;
use App\Models\Atc\Endorsement\Condition;
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

    /** @var Endorsement */
    private $endorsement;

    protected function setUp(): void
    {
        parent::setUp();

        $this->flag = factory(WaitingListFlag::class)->create();

        $this->waitingList = factory(WaitingList::class)->create();
        $this->waitingList->addFlag($this->flag);
        $this->waitingList->addToWaitingList($this->privacc, $this->privacc);

        $this->endorsement = factory(Endorsement::class)->create();
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
    public function itCanBeRelatedToAnEndorsement()
    {
        $this->assertNull($this->flag->endorsement);
        $this->flag->update(['endorsement_id' => $this->endorsement->id]);

        $this->assertNotNull($this->flag->fresh()->endorsement);
    }

    /** @test */
    public function itDetectsTrueValueForFlagWithEndorsement()
    {
        $account = factory(Account::class)->create();
        // populate network data
        factory(Atc::class)->create(['account_id' => $account->id, 'callsign' => 'EGGD_APP', 'minutes_online' => 61]);
        $condition = factory(Condition::class)->create(['required_hours' => 1, 'positions' => ['EGGD_APP']]);

        $flag = factory(WaitingListFlag::class)->create(['endorsement_id' => $condition->endorsement->id, 'list_id' => $this->waitingList->id]);

        // add to the waiting list
        handleService(new AddToWaitingList($this->waitingList, $account, $this->privacc));

        // find the pivot
        $waitingListAccount = $this->waitingList->accounts()->find($account->id)->pivot;

        $this->assertTrue($waitingListAccount->flags()->find($flag->id)->pivot->value);
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

    /** @test */
    public function itDetectsWhetherAllTheFlagsAreTrue()
    {
        $account = factory(Account::class)->create();
        // null list represents a flag which hasn't yet been assigned to list.
        // Normal flow wouldn't have this, but needs to emulate the action
        // all of the flags below have a default value of false.
        $flag = factory(WaitingListFlag::class)->create(['list_id' => null, 'default_value' => false]);

        $this->waitingList->addToWaitingList($account, $this->privacc);
        $this->waitingList->addFlag($flag);

        // finds the account in the waiting list and then marks the flags
        $waitingListAccount = $this->waitingList->accounts()->findOrFail($account->id)->pivot;
        $waitingListAccount->markFlag($flag);

        $this->assertTrue($waitingListAccount->fresh()->allFlagsChecker());
    }

    /** @test */
    public function itDetectsWhetherAnAccountShouldBeInTheActiveBucket()
    {
        $account = factory(Account::class)->create();
        // null list represents a flag which hasn't yet been assigned to list.
        // Normal flow wouldn't have this, but needs to emulate the action
        // all of the flags below have a default value of false.
        $flag = factory(WaitingListFlag::class)->create(['list_id' => null, 'default_value' => false]);

        // find the 'active' status
        $status = WaitingListStatus::find(WaitingListStatus::DEFAULT_STATUS);

        // an account is automatically allocated an active status (vital to know in the context of this test)
        $this->waitingList->addToWaitingList($account, $this->privacc);
        $this->waitingList->addFlag($flag);

        // finds the account in the waiting list
        $waitingListAccount = $this->waitingList->accounts()->findOrFail($account->id)->pivot;
        // then marks the flags
        $waitingListAccount->markFlag($flag);
        // and sets an active status
        $waitingListAccount->addStatus($status);

        // creates an atc session to simulate the 12 hour requirement stipulated within the model
        $atcSession = factory(Atc::class)->create(['account_id' => $account->id, 'minutes_online' => 721, 'disconnected_at' => now()]);

        $this->assertTrue($waitingListAccount->fresh()->eligibility);
    }

    /** @test */
    public function itShouldCheckWithOnlyOneFlagWhenAnyDefinedAsWaitingList()
    {
        $account = factory(Account::class)->create();
        $anyCheckerWaitingList = factory(WaitingList::class)->create(['flags_check' => WaitingList::ANY_FLAGS]);

        // create an atc session which will pass the 12 hour check hardcoded against a waiting list flags
        factory(Atc::class)->create(
            [
                'account_id' => $account->id,
                'minutes_online' => 721,
                'disconnected_at' => now(),
            ]);

        $anyCheckerWaitingList->addToWaitingList($account, $this->privacc);

        // create an ATC session and condition which pass
        factory(Atc::class)->create(['account_id' => $account->id, 'callsign' => 'EGGD_APP', 'minutes_online' => 61]);
        $condition = factory(Condition::class)->create(['required_hours' => 1, 'positions' => ['EGGD_APP']]);

        // create an endorsement condition which would not pass.
        $conditionNotMet = factory(Condition::class)->create(['required_hours' => 100, 'positions' => ['EGXX_APP']]);

        // create the two flags with a linked endorsement. Only one should be met, but that is acceptable for an 'ANY' check.
        $flag = factory(WaitingListFlag::class)->create(['endorsement_id' => $condition->endorsement->id, 'list_id' => null, 'default_value' => false]);
        $unmetFlag = factory(WaitingListFlag::class)->create(['endorsement_id' => $conditionNotMet->endorsement->id, 'list_id' => null, 'default_value' => false]);

        $anyCheckerWaitingList->addFlag($flag);
        $anyCheckerWaitingList->addFlag($unmetFlag);

        $waitingListAccount = $anyCheckerWaitingList->fresh()->accounts->find($account->id)->pivot;
        // find the 'active' status
        $status = WaitingListStatus::find(WaitingListStatus::DEFAULT_STATUS);
        // and sets an active status
        $waitingListAccount->addStatus($status);

        $this->assertTrue($waitingListAccount->fresh()->eligibility);
    }
}
