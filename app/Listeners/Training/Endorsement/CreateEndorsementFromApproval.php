<?php

namespace App\Listeners\Training\Endorsement;

use App\Events\Training\EndorsementRequestApproved;
use App\Models\Mship\Account\Endorsement;

class CreateEndorsementFromApproval
{
    /**
     * Handle the event.
     */
    public function handle(EndorsementRequestApproved $event): void
    {
        $endorsementRequest = $event->getEndorsementRequest();
        $endorsableEntity = $endorsementRequest->endorsable;

        Endorsement::create([
            'account_id' => $endorsementRequest->account_id,
            'endorsement_request_id' => $endorsementRequest->id,
            'created_by' => auth()->id(),
            'endorsable_type' => $endorsableEntity::class,
            'endorsable_id' => $endorsableEntity->id,
            'expired_at' => $endorsementRequest->endorsable_expired_at ?? null,
        ]);
    }
}
