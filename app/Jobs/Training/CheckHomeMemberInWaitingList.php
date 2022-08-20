<?php

namespace App\Jobs\Training;

use App\Models\Mship\Account;
use App\Models\Training\WaitingList;
use App\Notifications\Training\RemovedFromWaitingListNonHomeMember;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class CheckHomeMemberInWaitingList implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private WaitingList $waitingList;
    private Account $account;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(WaitingList $waitingList, Account $account)
    {
        $this->waitingList = $waitingList;
        $this->account = $account;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            $this->waitingList->accounts()->findOrFail($this->account->id);
        } catch (ModelNotFoundException) {
            Log::warning("Account {$this->account->id} not in waiting list.");

            return;
        }

        if (! $this->account->primary_state->isDivision) {
            $this->waitingList->removeFromWaitingList($this->account);
            $this->account->notify(new RemovedFromWaitingListNonHomeMember);
        }
    }
}
