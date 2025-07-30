<?php

namespace App\Jobs\Training;

use App\Models\Training\WaitingList\Removal;
use App\Models\Training\WaitingList\RemovalReason;
use App\Models\Training\WaitingList\WaitingListRetentionChecks;
use App\Notifications\Training\RemovedFromWaitingListFailedRetention;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class WaitingListRetentionRemoval implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public WaitingListRetentionChecks $retentionCheck) {}

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $this->retentionCheck->status = WaitingListRetentionChecks::STATUS_EXPIRED;
        $this->retentionCheck->removal_actioned_at = now();
        $this->retentionCheck->save();

        if (! $this->retentionCheck->waitingListAccount) {
            \Log::warning("WaitingListAccount not found for retention check {$this->retentionCheck->id}. Cannot remove from waiting list.");

            return;
        }

        $this->retentionCheck->waitingListAccount->account->notify(new RemovedFromWaitingListFailedRetention($this->retentionCheck));

        $account = $this->retentionCheck->waitingListAccount->account;

        $this->retentionCheck->waitingListAccount->waitingList->removeFromWaitingList($account, new Removal(RemovalReason::FailedRetention, null));
        \Log::info("Member {$account->id} was removed from waiting list  {$this->retentionCheck->waitingListAccount->waiting_list_id} due to failed retention check {$this->retentionCheck->id}
        ");
    }
}
