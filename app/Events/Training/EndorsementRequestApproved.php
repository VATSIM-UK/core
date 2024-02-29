<?php

namespace App\Events\Training;

use App\Models\Mship\Account\EndorsementRequest;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EndorsementRequestApproved
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(
        private EndorsementRequest $endorsementRequest,
        private ?int $days
    ) {
    }

    public function getEndorsementRequest(): EndorsementRequest
    {
        return $this->endorsementRequest;
    }

    public function getExpiryDate()
    {
        if ($this->days === null) {
            return null;
        }

        return now()->addDays($this->days)->endOfDay();
    }
}
