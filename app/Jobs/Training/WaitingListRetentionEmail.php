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

class WaitingListRetentionEmail implements ShouldQueue
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
        $verifyToken = bin2hex(random_bytes(16));

        $waitingListAccount = WaitingList::findWaitingListAccount($record->waiting_list_account_id);
        $account = Account::find($waitingListAccount->account_id);

        account->notify(new WaitingListRetentionChecks($record, $verifyToken));

        $record->status = WaitingListRetentionChecks::STATUS_PENDING;
        $record->token = $verifyToken;
        $record->expires_at = now()->addDays(7);
        $record->email_sent_at = now();
        $record->save();
    }
}
