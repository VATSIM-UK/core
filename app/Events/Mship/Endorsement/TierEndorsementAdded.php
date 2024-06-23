<?php

namespace App\Events\Mship\Endorsement;

use App\Models\Mship\Account;
use App\Models\Mship\Account\Endorsement;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TierEndorsementAdded
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(private Endorsement $endorsement, private Account $account) {}

    public function getAccount()
    {
        return $this->account;
    }

    public function getEndorsement()
    {
        return $this->endorsement;
    }
}
