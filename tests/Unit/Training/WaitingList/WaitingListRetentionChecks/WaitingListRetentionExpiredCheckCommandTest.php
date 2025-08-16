<?php

namespace Tests\Unit\Training\WaitingList\WaitingListRetentionChecks;

use App\Jobs\Training\ActionWaitingListRetentionCheckRemoval;
use App\Models\Training\WaitingList\WaitingListRetentionCheck;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Bus;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class WaitingListRetentionExpiredCheckCommandTest extends TestCase
{
    #[Test]
    public function test_dispatches_removal_jobs_for_expired_retention_checks()
    {
        Bus::fake();

        $retentionCheck = WaitingListRetentionCheck::factory()->create([
            'expires_at' => now()->subDays(1),
            'status' => WaitingListRetentionCheck::STATUS_PENDING,
        ]);

        $retentionCheckAlreadyExpired = WaitingListRetentionCheck::factory()->create([
            'expires_at' => now()->subDays(1),
            'status' => WaitingListRetentionCheck::STATUS_EXPIRED,
        ]);

        Artisan::call('waiting-lists:check-for-expired-retention-checks');

        Bus::assertDispatched(ActionWaitingListRetentionCheckRemoval::class, function ($job) use ($retentionCheck) {
            return $job->retentionCheck->id === $retentionCheck->id;
        });

        Bus::assertNotDispatched(ActionWaitingListRetentionCheckRemoval::class, function ($job) use ($retentionCheckAlreadyExpired) {
            return $job->retentionCheck->id === $retentionCheckAlreadyExpired->id;
        });
    }

    #[Test]
    public function test_does_not_dispatch_removal_jobs_for_non_expired_retention_checks()
    {
        Bus::fake();

        $retentionCheck = WaitingListRetentionCheck::factory()->create([
            'expires_at' => now()->addDays(1),
            'status' => WaitingListRetentionCheck::STATUS_PENDING,
        ]);

        Artisan::call('waiting-lists:check-for-expired-retention-checks');

        Bus::assertNotDispatched(ActionWaitingListRetentionCheckRemoval::class, function ($job) use ($retentionCheck) {
            return $job->retentionCheck->id === $retentionCheck->id;
        });
    }
}
