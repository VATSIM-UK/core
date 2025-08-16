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
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ActionWaitingListRetentionCheckRemoval implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public WaitingListRetentionCheck $retentionCheck) {}

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        if (! $this->retentionCheck->waitingListAccount) {
            Log::warning("WaitingListAccount not found for retention check {$this->retentionCheck->id}. Cannot remove from waiting list.");

            return;
        }

        /** @var Account $account */
        $account = $this->retentionCheck->waitingListAccount->account;
        try {
            $account->notify(new RemovedFromWaitingListFailedRetention($this->retentionCheck));
        } catch (Exception $e) {
            Log::error("Failed to notify account {$account->id} of failed retention check {$this->retentionCheck->id}: {$e->getMessage()}");
            // deliberately return here to avoid removing the account from the waiting list
            $this->fail($e);

            return;
        }

        $waitingList = $this->retentionCheck->waitingListAccount->waitingList;
        $waitingList->removeFromWaitingList($account, new Removal(RemovalReason::FailedRetention, null));

        Log::info("Member {$account->id} was removed from waiting list  {$waitingList->id} due to failed retention check {$this->retentionCheck->id}");

        WaitingListRetentionChecks::markRetentionCheckAsExpired($this->retentionCheck);
    }
}
