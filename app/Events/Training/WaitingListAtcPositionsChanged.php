<?php

namespace App\Events\Training;

use App\Events\Event;
use App\Models\Training\WaitingList;
use Illuminate\Queue\SerializesModels;

class WaitingListAtcPositionsChanged extends Event
{
    use SerializesModels;

    public WaitingList $waitingList;

    public function __construct(WaitingList $waitingList)
    {
        $this->waitingList = $waitingList;
    }
}
