<?php

namespace App\Events\Training;

use App\Models\Training\WaitingList;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class FlagAddedToWaitingList
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(private WaitingList $waitingList)
    {
    }

    public function getWaitingList(): WaitingList
    {
        return $this->waitingList;
    }
}
