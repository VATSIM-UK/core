<?php

namespace App\Events\Training;

use App\Models\Mship\Account;
use App\Models\Training\WaitingList;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AccountRemovedFromWaitingList
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $account;

    public $waitingList;

    public $staffAccount;

    /**
     * Create a new event instance.
     */
    public function __construct(Account $account, WaitingList $waitingList, Account $staffAccount)
    {
        $this->account = $account;
        $this->waitingList = $waitingList;
        $this->staffAccount = $staffAccount;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
