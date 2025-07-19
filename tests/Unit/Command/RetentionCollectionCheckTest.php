<?php

namespace Tests\Unit\Command;

use App\Console\Commands\Training\WaitingListRetentionChecks as CommandWaitingListRetentionChecks;
use App\Jobs\Training\WaitingListRetentionEmail;
use App\Jobs\Training\WaitingListRetentionRemoval;
use App\Models\Training\WaitingList\WaitingListRetentionChecks as RetentionChecksModel;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Bus;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class RetentionCollectionCheckTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_dispatches_removal_and_email_jobs_correctly()
    {
        Bus::fake();

        // Create a record to be removed
        $removeRecord = RetentionChecksModel::factory()->create([
            'expires_at' => now()->subDay(),
            'status' => RetentionChecksModel::STATUS_PENDING,
        ]);

        // Create a record to send email
        $emailRecord = RetentionChecksModel::factory()->create([
            'email_sent_at' => now()->subMonths(4),
        ]);

        $command = new CommandWaitingListRetentionChecks;
        $command->handle();

        Bus::assertDispatched(WaitingListRetentionRemoval::class, function ($job) use ($removeRecord) {
            return $job->retentionCheck->is($removeRecord);
        });

        Bus::assertDispatched(WaitingListRetentionEmail::class, function ($job) use ($emailRecord) {
            return $job->retentionCheck->is($emailRecord);
        });
    }
}
