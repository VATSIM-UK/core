<?php

namespace Tests\Unit\Training\WaitingList;

use App\Models\Mship\Account;
use App\Models\NetworkData\Atc;
use App\Models\Training\WaitingList;
use App\Models\Training\WaitingList\WaitingListStatus;
use App\Notifications\Training\WaitingListAtcTopTen;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class WaitingListTopTenNotificationTest extends TestCase
{
    use DatabaseTransactions, WaitingListTestHelper;

    public WaitingList $waitingList;

    public function setUp(): void
    {
        parent::setUp();

        $this->waitingList = $this->createList();

        Notification::fake();
    }

    /** @test */
    public function itSendsTopTenNotificationsOncePerAccount()
    {
        $status = WaitingListStatus::find(WaitingListStatus::DEFAULT_STATUS);
        $accounts = factory(Account::class, 10)->create()->each(function ($account) {
            factory(Atc::class)->create(['account_id' => $account->id, 'minutes_online' => 721, 'disconnected_at' => now()]);

            $this->waitingList->addToWaitingList($account, $this->privacc);
        });

        $this->waitingList->accounts()->each(function ($waitingListAccount) use ($status) {
            $waitingListAccount->pivot->addStatus($status);
        });

        Notification::fake();

        $this->artisan('waitinglists:sendatctoptennotification');

        Notification::assertCount(10, WaitingListAtcTopTen::class);
        Notification::assertSentTo([$accounts], WaitingListAtcTopTen::class);
    }

    /** @test */
    public function itDoesNotSendTopTenNotificationIfAccountAlreadyReceived()
    {
        $status = WaitingListStatus::find(WaitingListStatus::DEFAULT_STATUS);
        $accounts = factory(Account::class, 3)->create()->each(function ($account) {
            factory(Atc::class)->create(['account_id' => $account->id, 'minutes_online' => 721, 'disconnected_at' => now()]);

            $this->waitingList->addToWaitingList($account, $this->privacc);
        });
        $this->waitingList->accounts()->each(function ($waitingListAccount) use ($status) {
            $waitingListAccount->pivot->addStatus($status);
        });

        $waitingListAccounts = $this->waitingList->accounts();

        $accountToHaveAlreadySent = $waitingListAccounts->find($accounts[1]->id);
        $accountToHaveAlreadySent->pivot->within_top_ten_notification_sent_at = now();
        $accountToHaveAlreadySent->pivot->save();

        $this->artisan('waitinglists:sendatctoptennotification');

        Notification::assertCount(2, WaitingListAtcTopTen::class);

        Notification::assertSentTo([$accounts[0], $accounts[2]], WaitingListAtcTopTen::class);
    }

    /** @test */
    public function itDoesNotSendNotificationIfDropsOutAndBackInOfTopTen()
    {
        $status = WaitingListStatus::find(WaitingListStatus::DEFAULT_STATUS);
        $accounts = factory(Account::class, 10)->create()->each(function ($account) {
            factory(Atc::class)->create(['account_id' => $account->id, 'minutes_online' => 721, 'disconnected_at' => now()]);

            $this->waitingList->addToWaitingList($account, $this->privacc);
        });
        $this->waitingList->accounts()->each(function ($waitingListAccount) use ($status) {
            $waitingListAccount->pivot->addStatus($status);
        });

        $waitingListAccounts = $this->waitingList->accounts();

        $accountToDropOut = $waitingListAccounts->find($accounts[1]->id);
        $accountToDropOut->pivot->within_top_ten_notification_sent_at = now();
        $accountToDropOut->pivot->save();

        $this->artisan('waitinglists:sendatctoptennotification');

        Notification::assertNothingSentTo($accounts[1]);

        // make account drop out of the top 10 and eligibility all together
        $accountToDropOut->networkDataAtc()->delete();

        $this->artisan('waitinglists:sendatctoptennotification');

        Notification::assertNothingSentTo($accounts[1]);

        // make account eligible again
        factory(Atc::class)->create(['account_id' => $accountToDropOut->id, 'minutes_online' => 721, 'disconnected_at' => now()]);
        Notification::assertNothingSentTo($accounts[1]);
    }
}
