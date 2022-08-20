<?php

namespace Tests\Unit\Training\WaitingList;

use App\Jobs\Training\CheckHomeMemberInWaitingList;
use App\Models\Mship\Account;
use App\Models\Mship\State;
use App\Models\Training\WaitingList;
use App\Notifications\Training\RemovedFromWaitingListNonHomeMember;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class RemoveNonHomeMembersTest extends TestCase
{
    use DatabaseTransactions;

    private $waitingList;

    protected function setUp(): void
    {
        parent::setUp();

        $this->waitingList = factory(WaitingList::class)->create();
    }
    /** @test * */
    public function itWillRemoveMemberFromWaitingListWhenNotHomeMember()
    {
        $account = factory(Account::class)->create();
        $visitingState = State::findByCode("VISITING");
        $account->addState($visitingState);

        $this->waitingList->addToWaitingList($account->refresh(), $this->privacc);

        (new CheckHomeMemberInWaitingList($this->waitingList, $account))->handle();

        $this->assertFalse($this->waitingList->accounts->contains($account));
    }

    /** @test * */
    public function itWillNotRemoveHomeMembersFromWaitingList()
    {
        $account = factory(Account::class)->create();
        $visitingState = State::findByCode("DIVISION");
        $account->addState($visitingState);

        $this->waitingList->addToWaitingList($account->refresh(), $this->privacc);

        $job = new CheckHomeMemberInWaitingList($this->waitingList, $account);
        $job->handle();

        $this->assertTrue($this->waitingList->accounts->contains($account));
    }

        /** @test * */
    public function itWillDispatchNotificationWhenRemovingNonHomeMember()
    {
        $account = factory(Account::class)->create();
        $visitingState = State::findByCode("VISITING");
        $account->addState($visitingState);

        $this->waitingList->addToWaitingList($account->refresh(), $this->privacc);

        Notification::fake();

        $job = new CheckHomeMemberInWaitingList($this->waitingList, $account);
        $job->handle();

        Notification::assertSentTo($account, RemovedFromWaitingListNonHomeMember::class);
    }
}
