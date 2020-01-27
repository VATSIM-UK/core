<?php

namespace App\Events\Training;

use App\Models\Training\WaitingList;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class WaitingListCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $waitingList;

    /**
     * Create a new event instance.
     *
     * @param WaitingList $waitingList
     */
    public function __construct(WaitingList $waitingList)
    {
        $this->waitingList= $waitingList;
    }
}
