<?php

namespace Tests\Unit\Training\WaitingList;

use App\Jobs\Training\SendWaitingListRetentionCheckJob;
use App\Jobs\Training\SendWaitingListRemovalJob;
use App\Models\Training\WaitingList\WaitingListAccount;
use App\Models\Training\WaitingList\WaitingListRetentionChecks;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WaitingListRetentionChecksJobTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function it_queues_and_executes_retention_check_job()
    {
        Bus::fake();
        $account = WaitingListAccount::create([
            'account_id' => 1,
            'list_id' => 1,
        ]);
        $check = WaitingListRetentionChecks::create([
            'waiting_list_account_id' => $account->id,
            'token' => 'JOBTOKEN',
            'expires_at' => now()->addDays(7),
            'status' => 'pending',
            'email_sent_at' => now(),
        ]);
        SendWaitingListRetentionCheckJob::dispatch($check);
        Bus::assertDispatched(SendWaitingListRetentionCheckJob::class);
    }

    #[Test]
    public function it_queues_and_executes_removal_job()
    {
        Bus::fake();
        $account = WaitingListAccount::create([
            'account_id' => 2,
            'list_id' => 1,
        ]);
        $check = WaitingListRetentionChecks::create([
            'waiting_list_account_id' => $account->id,
            'token' => 'REMOVALJOBTOKEN',
            'expires_at' => now()->addDays(7),
            'status' => 'expired',
            'email_sent_at' => now()->subDays(10),
        ]);
        SendWaitingListRemovalJob::dispatch($check);
        Bus::assertDispatched(SendWaitingListRemovalJob::class);
    }
}
