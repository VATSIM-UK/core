<?php

namespace Tests\Feature\Jobs;

use App\Jobs\Mship\SyncToCTS;
use App\Models\Mship\Account;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SyncJobLoggingTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function a_failed_sync_job_logs_a_single_error_with_account_context()
    {
        $account = Account::factory()->create();

        Log::shouldReceive('error')->once()->withArgs(fn ($msg, $ctx) => $msg === 'Job failed'
            && $ctx['account_id'] === $account->id
            && $ctx['job'] === SyncToCTS::class
            && isset($ctx['exception'])
        );

        $job = new SyncToCTS($account);

        $job->failed(new \RuntimeException('boom'));
    }

    /**
     * NOTE: The success-path 'Member sync completed' info log (added to each
     * of the four sync jobs' handle() methods) is not exercised here because
     * handle() calls the real external sync method (syncToCTS/syncToHelpdesk/
     * syncUserToMoodle/syncToDiscord), which would perform live HTTP calls to
     * external services. That path is covered by manual/maintainer testing.
     */
}
