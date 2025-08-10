<?php

namespace Tests\Unit\Training\WaitingList\WaitingListRetentionChecks;

use App\Models\Mship\Account;
use App\Models\Training\WaitingList;
use App\Models\Training\WaitingList\WaitingListRetentionCheck;
use App\Services\Training\WaitingListRetentionChecks as WaitingListRetentionChecksService;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WaitingListRetentionChecksServiceTest extends TestCase
{
    #[Test]
    public function it_fetches_checks_which_have_passed_expiry_and_are_pending()
    {
        $expiredChecks = WaitingListRetentionCheck::factory()->create([
            'expires_at' => now()->subDays(1),
            'status' => WaitingListRetentionCheck::STATUS_PENDING,
        ]);
        $nonExpiredChecks = WaitingListRetentionCheck::factory()->create([
            'expires_at' => now()->addDays(1),
            'status' => WaitingListRetentionCheck::STATUS_PENDING,
        ]);

        $queriedExpiredChecks = WaitingListRetentionChecksService::getExpiredChecks(now());

        $this->assertTrue($queriedExpiredChecks->contains($expiredChecks));
        $this->assertFalse($queriedExpiredChecks->contains($nonExpiredChecks));
    }

    #[Test]
    public function it_creates_a_new_retention_check_record()
    {
        $waitingList = WaitingList::factory()->create([]);
        $account = Account::factory()->create([
            'id' => 1,
        ]);

        $waitingListAccount = $waitingList->addToWaitingList($account, $this->privacc);

        $retentionCheck = WaitingListRetentionChecksService::createRetentionCheckRecord($waitingListAccount);

        $this->assertDatabaseHas('training_waiting_list_retention_checks', [
            'waiting_list_account_id' => $waitingListAccount->id,
            'status' => WaitingListRetentionCheck::STATUS_PENDING,
            'token' => $retentionCheck->token,
            'expires_at' => $retentionCheck->expires_at,
            // email has not been sent yet so explicitly check for null
            'email_sent_at' => null,
        ]);
    }

    #[Test]
    public function it_marks_a_retention_check_as_expired()
    {
        $retentionCheck = WaitingListRetentionCheck::factory()->create([
            'status' => WaitingListRetentionCheck::STATUS_PENDING,
        ]);

        $retentionCheck = WaitingListRetentionChecksService::markRetentionCheckAsExpired($retentionCheck);

        $this->assertEquals(WaitingListRetentionCheck::STATUS_EXPIRED, $retentionCheck->status);
        $this->assertNotNull($retentionCheck->removal_actioned_at);
    }

    #[Test]
    public function it_marks_a_retention_check_as_used()
    {
        $retentionCheck = WaitingListRetentionCheck::factory()->create([
            'status' => WaitingListRetentionCheck::STATUS_PENDING,
        ]);

        $retentionCheck = WaitingListRetentionChecksService::markRetentionCheckAsUsed($retentionCheck);

        $this->assertEquals(WaitingListRetentionCheck::STATUS_USED, $retentionCheck->status);
        $this->assertNotNull($retentionCheck->response_at);
    }
}
