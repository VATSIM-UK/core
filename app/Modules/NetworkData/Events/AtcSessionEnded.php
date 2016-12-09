<?php

namespace App\Modules\NetworkData\Events;

use App\Events\Event;
use Illuminate\Queue\SerializesModels;
use App\Modules\NetworkData\Models\Atc;

class AtcSessionEnded extends Event
{
    use SerializesModels;

    public $atcSession = null;

    /**
     * Construct the event, storing the ATC session that's just ended.
     *
     * There's little to construct at the minute as it's simply a notification!
     */
    public function __construct(Atc $atc)
    {
        $this->atcSession = $atc;
    }
}
