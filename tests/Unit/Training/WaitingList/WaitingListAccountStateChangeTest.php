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
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class WaitingListAccountStateChangeTest extends TestCase
{
    use DatabaseTransactions;

    private WaitingList $waitingList;

    private WaitingList $nonHomeMembersOnlyWaitingList;

    protected function setUp(): void
    {
        parent::setUp();

        Event::fake();

        Notification::fake();

        $this->waitingList = factory(WaitingList::class)->create();
        $this->nonHomeMembersOnlyWaitingList = factory(WaitingList::class)->create();
        $this->nonHomeMembersOnlyWaitingList->home_members_only = 0;
        $this->nonHomeMembersOnlyWaitingList->save();
    }

    #[DataProvider('invalidStateProvider')]
    public function itShouldRemoveFromListWhenAccountIsAlteredToNonDivisionState(string $state)
    {
        $account = Account::factory()->create();
        $account->addState(State::findByCode('DIVISION'));
        $account->refresh();

        $this->waitingList->addToWaitingList($account, $this->privacc);

        $account->addState(State::findByCode($state));

        $event = new AccountAltered($account->refresh());
        (new CheckWaitingListAccountMshipState)->handle($event);

        $this->assertFalse($this->waitingList->accounts->contains($account));

        Notification::assertSentTo($account, RemovedFromWaitingListNonHomeMember::class);
    }

    #[DataProvider('invalidStateProvider')]
    public function itShouldNotRemoveFromNonHomeMembersOnlyListWhenAccountIsAlteredToNonDivisionState(string $state)
    {
        $account = Account::factory()->create();
        $account->addState(State::findByCode('DIVISION'));
        $account->refresh();

        $this->nonHomeMembersOnlyWaitingList->addToWaitingList($account, $this->privacc);

        $account->addState(State::findByCode($state));

        $event = new AccountAltered($account->refresh());
        (new CheckWaitingListAccountMshipState)->handle($event);

        $this->assertTrue($this->nonHomeMembersOnlyWaitingList->accounts->contains($account));
    }

    public static function invalidStateProvider(): array
    {
        return [
            ['INTERNATIONAL'],
            ['REGION'],
            ['UNKNOWN'],
        ];
    }

    /** @test */
    public function it_should_not_remove_from_list_when_division_state_does_not_change()
    {
        $account = Account::factory()->create();
        $account->addState(State::findByCode('DIVISION'));

        $this->waitingList->addToWaitingList($account, $this->privacc);

        // fresh call to retrieve the account from the database with the new state assigned.
        $event = new AccountAltered($account->fresh());
        (new CheckWaitingListAccountMshipState)->handle($event);

        $this->assertTrue($this->waitingList->includesAccount($account));

        Notification::assertNotSentTo($account, RemovedFromWaitingListNonHomeMember::class);
    }

    /** @test */
    public function it_should_not_send_notification_when_not_on_list_but_state_changed()
    {
        $account = Account::factory()->create();
        $account->addState(State::findByCode('DIVISION'));

        $event = new AccountAltered($account);
        (new CheckWaitingListAccountMshipState)->handle($event);

        $this->assertFalse($this->waitingList->includesAccount($account));

        Notification::assertNotSentTo($account, RemovedFromWaitingListNonHomeMember::class);
    }

    /** @test */
    public function it_should_remove_from_list_when_user_becomes_inactive_when_altered()
    {
        $account = Account::factory()->create();
        $account->inactive = false;
        $account->save();

        $this->waitingList->addToWaitingList($account, $this->privacc);

        $account->inactive = true;
        $account->save();

        $event = new AccountAltered($account->fresh());
        (new CheckWaitingListAccountInactivity)->handle($event);

        $this->assertFalse($this->waitingList->includesAccount($account));

        Notification::assertSentTo($account, RemovedFromWaitingListInactiveAccount::class);
    }

    /** @test */
    public function it_should_not_notify_inactive_account_not_on_list()
    {
        $account = Account::factory()->create();
        $account->inactive = true;
        $account->save();

        $event = new AccountAltered($account->fresh());
        (new CheckWaitingListAccountInactivity)->handle($event);

        Notification::assertNotSentTo($account, RemovedFromWaitingListInactiveAccount::class);
    }
}
