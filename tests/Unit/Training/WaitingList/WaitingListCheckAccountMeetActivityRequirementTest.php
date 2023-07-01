<?php

namespace Tests\Unit\Training\WaitingList;

use App\Models\Mship\Account;
use App\Models\NetworkData\Atc;
use App\Models\Training\WaitingList;
use App\Models\Training\WaitingList\WaitingListStatus;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class WaitingListCheckAccountsMeetActivityRequirementTest extends TestCase
{
    use DatabaseTransactions;

    private WaitingList $waitingList;

    public function setUp(): void
    {
        parent::setUp();

        $this->waitingList = factory(WaitingList::class)->create();
    }

    /** @test */
    public function itShouldMarkInactiveAccountForRemoval()
    {
        $account = factory(Account::class)->create()->refresh();

        $this->waitingList->addToWaitingList($account, $this->privacc);

        $status = WaitingListStatus::find(WaitingListStatus::DEFAULT_STATUS);

        $waitingListAccount = $this->waitingList->accounts()->findOrFail($account->id);
        $waitingListAccount->pivot->addStatus($status);

        $this->artisan('waitinglists:checkmembersmeetactivityrules');

        $this->assertNotNull($this->waitingList->accounts()->find($account->id)->pivot->pending_removal?->remove_at);
    }

    /** @test */
    public function itShouldNotMarkActiveAccountForRemoval()
    {
        $account = factory(Account::class)->create()->refresh();

        $this->waitingList->addToWaitingList($account, $this->privacc);

        $status = WaitingListStatus::find(WaitingListStatus::DEFAULT_STATUS);

        $waitingListAccount = $this->waitingList->accounts()->findOrFail($account->id);
        $waitingListAccount->pivot->addStatus($status);

        $atcSession = factory(Atc::class)->create(['account_id' => $account->id, 'minutes_online' => 721, 'disconnected_at' => now()]);

        $this->artisan('waitinglists:checkmembersmeetactivityrules');

        $this->assertDatabaseMissing('training_waiting_list_account_pending_removal',
            ['waiting_list_account_id' => $waitingListAccount->pivot->id]);
    }

    /** @test */
    public function itShouldCancelPendingRemovalWhenAccountBecomesActive()
    {
        $account = factory(Account::class)->create()->refresh();

        $this->waitingList->addToWaitingList($account, $this->privacc);

        $status = WaitingListStatus::find(WaitingListStatus::DEFAULT_STATUS);

        $waitingListAccount = $this->waitingList->accounts()->findOrFail($account->id);
        $waitingListAccount->pivot->addStatus($status);

        $this->artisan('waitinglists:checkmembersmeetactivityrules');

        $atcSession = factory(Atc::class)->create(['account_id' => $account->id, 'minutes_online' => 721, 'disconnected_at' => now()]);

        $this->artisan('waitinglists:checkmembersmeetactivityrules');

        $this->assertNull($this->waitingList->accounts()->find($account->id)->pivot->pending_removal?->remove_at);
    }

    /** @test */
    public function itShouldHandleUsersHoppingOnAndOffRemovalsList()
    {
        $account = factory(Account::class)->create()->refresh();

        $this->waitingList->addToWaitingList($account, $this->privacc);

        $status = WaitingListStatus::find(WaitingListStatus::DEFAULT_STATUS);

        $waitingListAccount = $this->waitingList->accounts()->findOrFail($account->id);
        $waitingListAccount->pivot->addStatus($status);

        $this->artisan('waitinglists:checkmembersmeetactivityrules');

        $this->assertNotNull($this->waitingList->accounts()->find($account->id)->pivot->pending_removal?->remove_at);

        $atcSession = factory(Atc::class)->create(['account_id' => $account->id, 'minutes_online' => 721, 'disconnected_at' => now()]);

        $this->artisan('waitinglists:checkmembersmeetactivityrules');

        $this->assertNull($this->waitingList->accounts()->find($account->id)->pivot->pending_removal?->remove_at);

        Atc::truncate();

        $this->artisan('waitinglists:checkmembersmeetactivityrules');

        $this->assertNotNull($this->waitingList->accounts()->find($account->id)->pivot->pending_removal?->remove_at);
    }
}
