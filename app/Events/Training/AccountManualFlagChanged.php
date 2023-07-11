<?php

namespace App\Events\Training;

use App\Contracts\AccountCentricEvent;
use App\Models\Mship\Account;
use App\Models\Training\WaitingList;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AccountManualFlagChanged implements AccountCentricEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(
        private Account $account,
        private WaitingList $waitingList,
    ) {
    }

    public function getAccount(): Account
    {
        return $this->account;
    }
}
