<?php

namespace App\Events\Training;

use App\Contracts\AccountCentricEvent;
use App\Models\Mship\Account;
use App\Models\Training\WaitingList;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AccountAddedToWaitingList implements AccountCentricEvent
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(public Account $account, public WaitingList $waitingList, public Account $staffAccount) {}

    public function getAccount(): Account
    {
        return $this->account;
    }
}
