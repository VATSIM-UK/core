<?php

namespace App\Events\NetworkData;

use App\Events\Event;
use App\Models\NetworkData\Atc;
use Illuminate\Queue\SerializesModels;

class AtcSessionEnded extends Event
{
    use SerializesModels;

    public $atcSession = null;

    /**
     * Construct the event, storing the ATC session that's just ended.
     *
     * There's little to construct at the minute as it's simply a notification!
     * @param Atc $atc
     */
    public function __construct(Atc $atc)
    {
        $this->atcSession = $atc;
    }
}
