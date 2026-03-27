<?php

namespace App\Jobs\Mship;

use App\Jobs\Job;
use App\Models\Mship\Account;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimitedWithRedis;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SyncToMoodle extends Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, SerializesModels;

    private Account $account;

    public $tries = 3;

    public $backoff = 30;

    public $queue = 'moodle';

    public function __construct(Account $account)
    {
        $this->account = $account;
    }

    public function handle(): void
    {
        Log::info('Starting MSHIP sync job.', $this->logContext());

        $this->account->syncUserToMoodle();

        Log::info('Completed MSHIP sync job.', $this->logContext());
    }

    public function getAccountId(): int
    {
        return $this->account->id;
    }

    public function middleware(): array
    {
        // Keep external API calls fast across accounts while serialising sync work per account.
        return [
            new RateLimitedWithRedis('moodle-sync'),
            (new WithoutOverlapping($this->getAccountId()))->releaseAfter(5)->expireAfter(120),
        ];
    }

    public function failed(Throwable $exception): void
    {
        Log::error('MSHIP sync job failed.', array_merge($this->logContext(), [
            'error' => $exception->getMessage(),
        ]));
    }

    private function logContext(): array
    {
        return [
            'job' => static::class,
            'account_id' => $this->account->id,
        ];
    }
}
