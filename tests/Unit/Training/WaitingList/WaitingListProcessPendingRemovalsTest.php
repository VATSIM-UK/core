<?php

namespace Tests\Unit\Training\WaitingList;

use App\Models\Mship\Account;
use App\Models\Training\WaitingList;
use App\Models\Training\WaitingList\WaitingListStatus;
use App\Notifications\Training\WaitingListRemovalCompleted;
use App\Notifications\Training\WaitingListRemovalReminder;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class WaitingListProcessPendingRemovalsTest extends TestCase
{
    use DatabaseTransactions;

    private WaitingList $waitingList;

    public function setUp(): void
    {
        parent::setUp();

        $this->waitingList = factory(WaitingList::class)->create();
    }

    /** @test */
    public function itShouldSendAFiveDayReminder()
    {
        $account = factory(Account::class)->create()->refresh();

        $this->waitingList->addToWaitingList($account, $this->privacc);

        $status = WaitingListStatus::find(WaitingListStatus::DEFAULT_STATUS);

        $waitingListAccount = $this->waitingList->accounts()->findOrFail($account->id);
        $waitingListAccount->pivot->addStatus($status);

        $removalDate = Carbon::now();
        $removalDate->addDays(5);
        $waitingListAccount->pivot->addPendingRemoval($removalDate);

        Notification::fake();

        $this->artisan('waitinglists:processpendingremovals');

        $this->assertDatabaseHas('training_waiting_list_account_pending_removal',
            ['waiting_list_account_id' => $waitingListAccount->pivot->id, 'emails_sent' => 1]);

        Notification::assertSentTo($account, WaitingListRemovalReminder::class);
    }

    /** @test */
    public function itShouldRemoveInactiveMembers()
    {
        $account = factory(Account::class)->create()->refresh();

        $this->waitingList->addToWaitingList($account, $this->privacc);

        $status = WaitingListStatus::find(WaitingListStatus::DEFAULT_STATUS);

        $waitingListAccount = $this->waitingList->accounts()->findOrFail($account->id);
        $waitingListAccount->pivot->addStatus($status);

        $removalDate = Carbon::now();
        $removalDate->subDays(1);
        $waitingListAccount->pivot->addPendingRemoval($removalDate);
        $waitingListAccount->pivot->pending_removal->incrementEmailCount();

        Notification::fake();

        $this->artisan('waitinglists:processpendingremovals');

        $this->assertNull($this->waitingList->accounts()->find($account->id), 'Waiting list account was deleted');

        $this->assertDatabaseHas('training_waiting_list_account_pending_removal',
            ['waiting_list_account_id' => $waitingListAccount->pivot->id, 'status' => 'Completed']);

        Notification::assertSentTo($account, WaitingListRemovalCompleted::class);
    }
}
