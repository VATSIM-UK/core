<?php

namespace App\Jobs\Training;

use App\Models\Training\WaitingList\WaitingListAccount;
use App\Notifications\Training\WaitingListRetentionCheckAccountNotification;
use App\Services\Training\WaitingListRetentionChecks as WaitingListRetentionChecksService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\Middleware\RateLimitedWithRedis;
use Illuminate\Queue\Middleware\WithoutOverlapping;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendWaitingListRetentionCheck implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    public $backoff = 30;

    public function __construct(public WaitingListAccount $waitingListAccount)
    {
        $this->onQueue('training-retention');
    }

    public function handle(): void
    {
        Log::info('Starting waiting list retention check notification job.', $this->logContext());

        DB::beginTransaction();

        $retentionCheck = WaitingListRetentionChecksService::createRetentionCheckRecord($this->waitingListAccount);

        try {
            $this->waitingListAccount->account->notify(new WaitingListRetentionCheckAccountNotification($retentionCheck));
        } catch (Exception $exception) {
            Log::error("Failed to notify account {$this->waitingListAccount->account->id} of retention check {$retentionCheck->id}: {$exception->getMessage()}", $this->logContext());

            DB::rollBack();
            $this->fail($exception);

            return;
        }

        DB::commit();

        Log::info('Completed waiting list retention check notification job.', $this->logContext());
    }

    public function middleware(): array
    {
        // Throughput is increased across accounts, while duplicate sends for a single waiting list account are prevented.
        return [
            new RateLimitedWithRedis('training-retention-check-send'),
            (new WithoutOverlapping("waiting-list-account:{$this->waitingListAccount->id}"))->releaseAfter(5)->expireAfter(180),
        ];
    }

    public function failed(Throwable $exception): void
    {
        Log::error('Waiting list retention check notification job failed.', [
            ...$this->logContext(),
            'error' => $exception->getMessage(),
        ]);
    }

    private function logContext(): array
    {
        return [
            'job' => static::class,
            'waiting_list_account_id' => $this->waitingListAccount->id,
            'account_id' => $this->waitingListAccount->account_id,
        ];
    }
}
