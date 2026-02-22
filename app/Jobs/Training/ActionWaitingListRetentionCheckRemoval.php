<?php

namespace App\Jobs\Training;

use App\Models\Training\WaitingList\Removal;
use App\Models\Training\WaitingList\RemovalReason;
use App\Models\Training\WaitingList\WaitingListRetentionCheck;
use App\Notifications\Training\RemovedFromWaitingListFailedRetention;
use App\Services\Training\WaitingListRetentionChecks;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimitedWithRedis;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ActionWaitingListRetentionCheckRemoval implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public $backoff = 30;

    public $queue = 'training-retention';

    public function __construct(public WaitingListRetentionCheck $retentionCheck) {}

    public function handle(): void
    {
        Log::info('Starting waiting list retention check removal job.', $this->logContext());

        if (! $this->retentionCheck->waitingListAccount) {
            Log::warning('WaitingListAccount not found. Cannot remove from waiting list.', $this->logContext());

            return;
        }

        $account = $this->retentionCheck->waitingListAccount->account;

        try {
            $account->notify(new RemovedFromWaitingListFailedRetention($this->retentionCheck));
        } catch (Exception $exception) {
            Log::error("Failed to notify account {$account->id} of failed retention check {$this->retentionCheck->id}: {$exception->getMessage()}", $this->logContext());

            // Deliberately fail and stop here to avoid removing the account when the notification did not send.
            $this->fail($exception);

            return;
        }

        $waitingList = $this->retentionCheck->waitingListAccount->waitingList;
        $waitingList->removeFromWaitingList($account, new Removal(RemovalReason::FailedRetention, null));

        Log::info("Member {$account->id} was removed from waiting list {$waitingList->id} due to failed retention check {$this->retentionCheck->id}", $this->logContext());

        WaitingListRetentionChecks::markRetentionCheckAsExpired($this->retentionCheck);

        Log::info('Completed waiting list retention check removal job.', $this->logContext());
    }

    public function middleware(): array
    {
        // Allow concurrent removals for different checks while avoiding duplicate processing of the same check.
        return [
            new RateLimitedWithRedis('training-retention-check-removal'),
            (new WithoutOverlapping("retention-check:{$this->retentionCheck->id}"))->releaseAfter(5)->expireAfter(180),
        ];
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Waiting list retention check removal job failed.', [
            ...$this->logContext(),
            'error' => $exception->getMessage(),
        ]);
    }

    private function logContext(): array
    {
        return [
            'job' => static::class,
            'retention_check_id' => $this->retentionCheck->id,
            'waiting_list_account_id' => $this->retentionCheck->waiting_list_account_id,
        ];
    }
}
