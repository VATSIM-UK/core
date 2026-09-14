<?php

namespace App\Jobs\Training;

use App\Models\Training\WaitingList\Removal;
use App\Models\Training\WaitingList\RemovalReason;
use App\Models\Training\WaitingList\WaitingListRetentionCheck;
use App\Models\VisitTransfer\Application;
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
            Log::warning('WaitingListAccount not found for retention check. Cannot remove from waiting list', ['retention_check_id' => $this->retentionCheck->id]);

            return;
        }

        /** @var Account $account */
        $account = $this->retentionCheck->waitingListAccount->account;
        try {
            $account->notify(new RemovedFromWaitingListFailedRetention($this->retentionCheck));
        } catch (Exception $e) {
            Log::error('Failed to notify account of failed retention check', [
                'account_id' => $account->id,
                'retention_check_id' => $this->retentionCheck->id,
                'exception' => $e,
            ]);
            // deliberately return here to avoid removing the account from the waiting list
            $this->fail($e);

            return;
        }

        $waitingList = $this->retentionCheck->waitingListAccount->waitingList;
        $waitingList->removeFromWaitingList($account, new Removal(RemovalReason::FailedRetention, null));

        if ($waitingList->is_vt) {
            Application::where('account_id', $account->id)
                ->where('status', Application::STATUS_ACCEPTED)
                ->get()
                ->each(function (Application $application) {
                    $application->cancel('Your visiting/transfer application has been cancelled as you were removed from the waiting list due to a failed retention check.');
                });
        }

        Log::info('Member was removed from waiting list due to failed retention check', [
            'account_id' => $account->id,
            'waiting_list_id' => $waitingList->id,
            'retention_check_id' => $this->retentionCheck->id,
        ]);

        WaitingListRetentionChecks::markRetentionCheckAsExpired($this->retentionCheck);
    }
}
