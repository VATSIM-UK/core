<?php

namespace App\Jobs\Training;

use App\Models\Mship\Account;
use App\Models\Training\WaitingList;
use App\Models\Training\WaitingList\WaitingListRetentionChecks;
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
        $record = $this->retentionCheck;
        $record->status = WaitingListRetentionChecks::STATUS_EXPIRED;
        $record->removal_actioned_at = now();
        $record->save();

        $waitingListAccount = WaitingList::findWaitingListAccount($record->waiting_list_account_id);
        if ($waitingListAccount) {
            WaitingList::removeAccountFromWaitingList($waitingListAccount->account, 'Expired retention check');
        }
    }
}
