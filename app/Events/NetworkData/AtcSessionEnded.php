<?php

namespace App\Events\NetworkData;

use App\Contracts\AccountCentricEvent;
use App\Events\Event;
use App\Models\Mship\Account;
use App\Models\NetworkData\Atc;
use Illuminate\Queue\SerializesModels;

class AtcSessionEnded extends Event implements AccountCentricEvent
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

    public function getAccount(): Account
    {
        return $this->atcSession->load('account')->account;
    }
}
