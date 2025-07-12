<?php

namespace App\Jobs\Training;

use App\Models\Mship\Account;
use App\Models\Training\WaitingList;
use App\Models\Training\WaitingList\WaitingListAccount;
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
        $waitingListAccount = WaitingList::findWaitingListAccount($this->retentionCheck->waiting_list_account_id);
        $account = Account::find($waitingListAccount->account_id);

        $this->retentionCheck->status = WaitingListRetentionChecks::STATUS_EXPIRED;
        $this->retentionCheck->removal_actioned_at = now();
        $this->retentionCheck->save();

        $account->notify(new RemovedFromWaitingListFailedRetention($this->retentionCheck));

        if ($waitingListAccount) {
            WaitingList::removeAccountFromWaitingList($waitingListAccount->account, 'Expired retention check');
        }
    }
}
