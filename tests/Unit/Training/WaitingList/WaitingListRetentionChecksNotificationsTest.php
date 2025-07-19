<?php

namespace Tests\Unit\Training\WaitingList;

use App\Jobs\Training\WaitingListRetentionEmail;
use App\Jobs\Training\WaitingListRetentionRemoval;
use App\Models\Mship\Account;
use App\Models\Training\WaitingList\WaitingListRetentionChecks;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WaitingListRetentionChecksNotificationsTest extends TestCase
{
    use DatabaseTransactions, WaitingListTestHelper;

    #[Test]
    public function it_sends_retention_email_and_updates_record()
    {
        $waitingList = $this->createList();
        $account = Account::factory()->create([
            'id' => 1,
        ]);

        $waitingListAccount = $waitingList->addToWaitingList($account, $this->privacc);

        $oldCheck = WaitingListRetentionChecks::factory()->create([
            'waiting_list_account_id' => $waitingListAccount->id,
            'status' => WaitingListRetentionChecks::STATUS_USED,
            'token' => 'used_token',
            'expires_at' => now()->subMonths(3)->addDays(1),
            'email_sent_at' => now()->subMonths(3),
        ]);

        $job = new WaitingListRetentionEmail($oldCheck);
        $job->handle();

        $generatedRetentionCheck = WaitingListRetentionChecks::where('waiting_list_account_id', $waitingListAccount->id)
            ->where('status', WaitingListRetentionChecks::STATUS_PENDING)
            ->first();

        $this->assertEquals(WaitingListRetentionChecks::STATUS_PENDING, $generatedRetentionCheck->status);
        $this->assertNotNull($generatedRetentionCheck->token);
        $this->assertTrue($generatedRetentionCheck->expires_at->isFuture());
        $this->assertTrue($generatedRetentionCheck->email_sent_at->isPast() || $generatedRetentionCheck->email_sent_at->equalTo(now()));
    }

    #[Test]
    public function it_sends_removal_email()
    {
        $waitingList = $this->createPopulatedList();
        $waitingListAccount = $waitingList->waitingListAccounts()->first();

        $retentionCheck = WaitingListRetentionChecks::factory()->create([
            'waiting_list_account_id' => $waitingListAccount->id,
            'status' => WaitingListRetentionChecks::STATUS_PENDING,
            'token' => 'test_token',
            'expires_at' => now()->addDays(7),
            'email_sent_at' => now(),
        ]);

        $job = new WaitingListRetentionRemoval($retentionCheck);
        $job->handle();

        $retentionCheck->refresh();
        $this->assertEquals(WaitingListRetentionChecks::STATUS_EXPIRED, $retentionCheck->status);
        $this->assertTrue($retentionCheck->removal_actioned_at->isPast() || $retentionCheck->removal_actioned_at->equalTo(now()));

        $this->assertNull($retentionCheck->waitingListAccount);

    }
}
