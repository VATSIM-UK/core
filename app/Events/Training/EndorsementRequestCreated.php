<?php

namespace App\Events\Training;

use App\Models\Mship\Account\EndorsementRequest;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class EndorsementRequestCreated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance.
     */
    public function __construct(private EndorsementRequest $endorsementRequest)
    {
    }

    public function getEndorsementRequest(): EndorsementRequest
    {
        return $this->endorsementRequest->load(['requester', 'endorsable', 'account']);
    }
}
