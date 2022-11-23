<?php

namespace Tests\Unit\Training\WaitingList;

use App\Events\Mship\AccountAltered;
use App\Listeners\Training\WaitingList\CheckWaitingListAccountInactivity;
use App\Listeners\Training\WaitingList\CheckWaitingListAccountMshipState;
use App\Models\Mship\Account;
use App\Models\Mship\State;
use App\Models\Training\WaitingList;
use App\Notifications\Training\RemovedFromWaitingListInactiveAccount;
use App\Notifications\Training\RemovedFromWaitingListNonHomeMember;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class WaitingListAccountStateChangeTest extends TestCase
{
    use DatabaseTransactions;

    private WaitingList $waitingList;

    public function setUp(): void
    {
        parent::setUp();

        Event::fake();

        Notification::fake();

        $this->waitingList = factory(WaitingList::class)->create();
    }

    /** @test */
    public function itShouldRemoveFromListWhenAccountIsAlteredToNonDivisionState()
    {
        $account = factory(Account::class)->create();
        $account->addState(State::findByCode('DIVISION'));

        $this->waitingList->addToWaitingList($account, $this->privacc);

        $account->fresh()->addState(State::findByCode('VISITING'), 'EUR', 'EUD');

        $event = new AccountAltered($account);
        (new CheckWaitingListAccountMshipState())->handle($event);

        $this->assertFalse($this->waitingList->accounts->contains($account));

        Notification::assertSentTo($account, RemovedFromWaitingListNonHomeMember::class);
    }

    /** @test */
    public function itShouldNotRemoveFromListWhenDivisionStateDoesNotChange()
    {
        $account = factory(Account::class)->create();
        $account->addState(State::findByCode('DIVISION'));

        $this->waitingList->addToWaitingList($account, $this->privacc);

        // fresh call to retrieve the account from the database with the new state assigned.
        $event = new AccountAltered($account->fresh());
        (new CheckWaitingListAccountMshipState())->handle($event);

        $this->assertTrue($this->waitingList->accounts->contains($account));

        Notification::assertNotSentTo($account, RemovedFromWaitingListNonHomeMember::class);
    }

    /** @test */
    public function itShouldNotSendNotificationWhenNotOnListButStateChanged()
    {
        $account = factory(Account::class)->create();
        $account->addState(State::findByCode('DIVISION'));

        $event = new AccountAltered($account);
        (new CheckWaitingListAccountMshipState())->handle($event);

        $this->assertFalse($this->waitingList->accounts->contains($account));

        Notification::assertNotSentTo($account, RemovedFromWaitingListNonHomeMember::class);
    }

    /** @test */
    public function itShouldRemoveFromListWhenUserBecomesInactiveWhenAltered()
    {
        $account = factory(Account::class)->create();
        $account->inactive = false;
        $account->save();

        $this->waitingList->addToWaitingList($account, $this->privacc);

        $account->inactive = true;
        $account->save();

        $event = new AccountAltered($account->fresh());
        (new CheckWaitingListAccountInactivity())->handle($event);

        $this->assertFalse($this->waitingList->accounts->contains($account));

        Notification::assertSentTo($account, RemovedFromWaitingListInactiveAccount::class);
    }

    /** @test */
    public function itShouldNotNotifyInactiveAccountNotOnList()
    {
        $account = factory(Account::class)->create();
        $account->inactive = true;
        $account->save();

        $event = new AccountAltered($account->fresh());
        (new CheckWaitingListAccountInactivity())->handle($event);

        Notification::assertNotSentTo($account, RemovedFromWaitingListInactiveAccount::class);
    }
}
