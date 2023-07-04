<?php

namespace Tests\Feature\Training;

use App\Models\Mship\Account;
use App\Models\Mship\State;
use App\Models\Training\WaitingList;
use App\Notifications\Training\RemovedFromWaitingListInactiveAccount;
use App\Notifications\Training\RemovedFromWaitingListNonHomeMember;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class WaitingListInactivityIntegrationTest extends TestCase
{
    use DatabaseTransactions;

    public function setUp(): void
    {
        parent::setUp();

        Notification::fake();
    }

    /** @test */
    public function itShouldReactToRealAccountAlteredEventForInactivity()
    {
        $this->markTestSkipped('The event is not fired as it is currently disabled.');

        $account = Account::factory()->create(['inactive' => false]);
        $account->addState(State::findByCode('DIVISION'));

        $waitingList = factory(WaitingList::class)->create();
        $waitingList->addToWaitingList($account, $this->privacc);

        $this->assertTrue($waitingList->accounts->contains($account));

        // saving the inactive account will trigger the AccountAltered event.
        $account->refresh();
        $account->inactive = true;
        $account->save();

        $this->assertFalse($waitingList->refresh()->accounts->contains($account));
        Notification::assertSentToTimes($account, RemovedFromWaitingListInactiveAccount::class, 1);
    }

    /** @test */
    public function itShouldReactToRealAccountAlteredEventForInactivityNotOnList()
    {
        $this->markTestSkipped('The event is not fired as it is currently disabled.');

        $account = Account::factory()->create();
        $account->addState(State::findByCode('DIVISION'));

        $waitingList = factory(WaitingList::class)->create();

        $this->assertFalse($waitingList->accounts->contains($account));

        $account->inactive = true;
        $account->save();

        $this->assertFalse($waitingList->fresh()->accounts->contains($account));
        Notification::assertNothingSentTo($account, RemovedFromWaitingListInactiveAccount::class);
    }

    /** @test
     * @group test1
     */
    public function itShouldReactToRealAccountAlteredEventForStateChanged()
    {
        $account = Account::factory()->create();
        $account->addState(State::findByCode('DIVISION'));
        $account->refresh();

        $waitingList = factory(WaitingList::class)->create();
        $waitingList->addToWaitingList($account, $this->privacc);

        $this->assertTrue($waitingList->accounts->contains($account));

        $account->addState(State::findByCode('REGION'));

        $this->assertFalse($waitingList->fresh()->accounts->contains($account));
        Notification::assertSentToTimes($account, RemovedFromWaitingListNonHomeMember::class, 1);
    }

    /** @test */
    public function itShouldReactToRealAccountAlteredEventForMshipStateNotOnList()
    {
        $account = Account::factory()->create();
        $account->addState(State::findByCode('DIVISION'));

        $waitingList = factory(WaitingList::class)->create();

        $this->assertFalse($waitingList->accounts->contains($account));

        $account->updateDivision('EUD', 'EUR');
        $account->refresh();

        $this->assertFalse($waitingList->fresh()->accounts->contains($account));
        Notification::assertNothingSentTo($account, RemovedFromWaitingListNonHomeMember::class);
    }
}
