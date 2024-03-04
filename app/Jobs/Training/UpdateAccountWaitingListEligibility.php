<?php

namespace App\Jobs\Training;

use App\Models\Mship\Account;
use App\Services\Training\CheckWaitingListFlags;
use App\Services\Training\WriteWaitingListFlagSummary;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateAccountWaitingListEligibility implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(public Account $account)
    {
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $service = new CheckWaitingListFlags($this->account);

        $accountWaitingLists = $this->account->currentWaitingLists;

        foreach ($accountWaitingLists as $waitingList) {
            WriteWaitingListFlagSummary::handle($waitingList, $service);
        }
    }
}
