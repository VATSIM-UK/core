<?php

namespace App\Listeners\Training\Endorsement;

use App\Events\Training\EndorsementRequestApproved;
use App\Models\Mship\Account\Endorsement;
use App\Services\Training\EndorsementService;

class CreateEndorsementFromApproval
{
    /**
     * Handle the event.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function handle(EndorsementRequestApproved $event): void
    {
        $endorsementRequest = $event->getEndorsementRequest();
        $endorsableEntity = $endorsementRequest->endorsable;

        EndorsementService::assertDurationIsAllowed(
            $endorsableEntity,
            $endorsementRequest->account,
            $event->getDays(),
        );

        $createdBy = auth()->id();

        $endorsement = Endorsement::create([
            'account_id' => $endorsementRequest->account_id,
            'endorsement_request_id' => $endorsementRequest->id,
            'created_by' => $createdBy,
            'endorsable_type' => $endorsableEntity::class,
            'endorsable_id' => $endorsableEntity->id,
            'expires_at' => $event->getExpiryDate(),
        ]);

        $endorsementRequest->markApproved();

        audit('Endorsement created from approval', [
            'account_id' => $endorsementRequest->account_id,
            'endorsement_id' => $endorsement->id,
            'created_by' => $createdBy,
        ]);
    }
}
