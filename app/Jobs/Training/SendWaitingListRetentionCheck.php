<?php

namespace App\Jobs\Training;

use App\Models\Training\WaitingList\WaitingListAccount;
use App\Notifications\Training\WaitingListRetentionCheckNotification;
use App\Services\Training\WaitingListRetentionChecks as WaitingListRetentionChecksService;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SendWaitingListRetentionCheck implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public WaitingListAccount $waitingListAccount) {}

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        DB::beginTransaction();

        $retentionCheck = WaitingListRetentionChecksService::createRetentionCheckRecord($this->waitingListAccount);

        try {
            $this->waitingListAccount->account->notify(new WaitingListRetentionCheckNotification($retentionCheck));
        } catch (Exception $e) {
            Log::error("Failed to notify account {$this->waitingListAccount->account->id} of retention check {$retentionCheck->id}: {$e->getMessage()}");
            DB::rollBack();
            $this->fail($e);

            return;
        }

        DB::commit();
    }
}
