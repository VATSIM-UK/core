<?php

namespace App\Jobs\Training;

use App\Models\Mship\Account;
use App\Services\Training\CheckWaitingListFlags;
use App\Services\Training\WriteWaitingListFlagSummary;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimitedWithRedis;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class UpdateAccountWaitingListEligibility implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public $backoff = 15;

    public $queue = 'training-eligibility';

    public function __construct(public Account $account) {}

    public function handle(): void
    {
        Log::info('Starting waiting list eligibility update job.', $this->logContext());

        $service = new CheckWaitingListFlags($this->account);
        $accountWaitingLists = $this->account->currentWaitingLists();

        // Process only the account's current waiting lists, keeping per-list updates local and quick.
        foreach ($accountWaitingLists as $waitingList) {
            WriteWaitingListFlagSummary::handle($waitingList, $service);
        }

        Log::info('Completed waiting list eligibility update job.', [
            ...$this->logContext(),
            'waiting_list_count' => $accountWaitingLists->count(),
        ]);
    }

    public function middleware(): array
    {
        // We can update many accounts in parallel, but avoid overlapping updates for the same account.
        return [
            new RateLimitedWithRedis('training-eligibility-update'),
            (new WithoutOverlapping("account:{$this->account->id}"))->releaseAfter(5)->expireAfter(120),
        ];
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Waiting list eligibility update job failed.', [
            ...$this->logContext(),
            'error' => $exception->getMessage(),
        ]);
    }

    private function logContext(): array
    {
        return [
            'job' => static::class,
            'account_id' => $this->account->id,
        ];
    }
}
