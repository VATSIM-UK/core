<?php

namespace App\Events\Training;

use App\Models\Mship\Account;
use App\Models\Training\WaitingList;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class AccountRemovedFromWaitingList
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $account;

    public $waitingList;

    /**
     * Create a new event instance.
     *
     * @param Account $account
     * @param WaitingList $waitingList
     */
    public function __construct(Account $account, WaitingList $waitingList)
    {
        $this->account = $account;
        $this->waitingList = $waitingList;
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
