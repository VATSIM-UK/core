<?php

namespace Tests\Unit\Jobs;

use App\Jobs\Concerns\LogsJobLifecycle;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Tests\TestCase;

class LogsJobLifecycleTest extends TestCase
{
    /** @test */
    public function failed_logs_an_error_with_job_context()
    {
        $job = new class
        {
            use LogsJobLifecycle;

            protected function logJobContext(): array
            {
                return ['account_id' => 42];
            }
        };

        Log::shouldReceive('error')->once()->withArgs(fn ($msg, $ctx) => $msg === 'Job failed' && $ctx['account_id'] === 42 && isset($ctx['exception'])
        );

        $job->failed(new RuntimeException('boom'));
    }
}
