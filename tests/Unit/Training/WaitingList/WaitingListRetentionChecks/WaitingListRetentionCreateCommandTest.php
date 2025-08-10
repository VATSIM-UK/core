<?php

namespace Tests\Unit\Training\WaitingList\WaitingListRetentionChecks;

use App\Models\Mship\Account;
use App\Models\Training\WaitingList;
use App\Models\Training\WaitingList\WaitingListRetentionCheck;
use App\Notifications\Training\WaitingListRetentionCheckNotification;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Notification;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WaitingListRetentionCreateCommandTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function test_does_not_create_retention_checks_if_retention_checks_are_disabled()
    {
        $waitingList = WaitingList::factory()->create([
            'retention_checks_enabled' => false,
        ]);

        $account = Account::factory()->create();
        /**
         * This otherwise would create a retention check were it not for the
         * retention_checks_enabled flag being false.
         */
        $waitingList->retention_checks_enabled = false;
        $waitingList->save();

        $waitingListAccount = $waitingList->addToWaitingList($account, $this->privacc, now()->subMonths(3)->subDays(1));

        Artisan::call('waiting-lists:create-retention-checks');

        $this->assertDatabaseMissing('training_waiting_list_retention_checks', [
            'waiting_list_account_id' => $waitingListAccount->id,
        ]);
    }

    #[Test]
    public function test_creates_retention_checks_for_account_after_three_months_on_list_with_no_previous_checks()
    {
        Notification::fake();

        $waitingList = WaitingList::factory()->create([
            'retention_checks_enabled' => true,
            'retention_checks_months' => 3,
        ]);

        $account = Account::factory()->create();
        $waitingListAccount = $waitingList->addToWaitingList($account, $this->privacc, now()->subMonths(3)->subDays(1));

        Artisan::call('waiting-lists:create-retention-checks');

        $this->assertDatabaseHas('training_waiting_list_retention_checks', [
            'waiting_list_account_id' => $waitingListAccount->id,
            'status' => WaitingListRetentionCheck::STATUS_PENDING,
        ]);
        Notification::assertSentTo($account, WaitingListRetentionCheckNotification::class);
    }
}
