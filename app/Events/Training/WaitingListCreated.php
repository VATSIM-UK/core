<?php

namespace App\Events\Training;

use App\Models\Training\WaitingList;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WaitingListCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $waitingList;

    /**
     * Create a new event instance.
     */
    public function __construct(WaitingList $waitingList)
    {
        $this->waitingList = $waitingList;
    }
}
