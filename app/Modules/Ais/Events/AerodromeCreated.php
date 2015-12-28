<?php

namespace App\Modules\Ais\Events;

use App\Events\Event;

use App\Modules\Ais\Models\Aerodrome;
use Illuminate\Queue\SerializesModels;

class AerodromeCreated extends Event {
    use SerializesModels;

    public $aerodrome = null;

    /**
     * Construct the event, storing the aerodrome that's just been created.
     *
     * There's little to construct at the minute as it's simply a notification!
     *
     * @param Aerodrome $aerodrome
     */
    public function __construct(Aerodrome $aerodrome){
        $this->aerodrome = $aerodrome;
    }
}