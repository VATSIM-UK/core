<?php

namespace Tests\Unit\Jobs;

use App\Jobs\Concerns\LogsJobFailure;
use Illuminate\Support\Facades\Log;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use Tests\TestCase;

class LogsJobLifecycleTest extends TestCase
{
    #[Test]
    public function failed_logs_an_error_with_job_context()
    {
        $job = new class
        {
            use LogsJobFailure;

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
