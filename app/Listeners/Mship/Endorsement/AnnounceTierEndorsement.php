<?php

namespace App\Listeners\Mship\Endorsement;

use App\Events\Mship\Endorsement\TierEndorsementAdded;
use App\Models\Atc\PositionGroup;
use App\Services\Training\TrainingSuccessesAnnouncementService;
use Illuminate\Contracts\Queue\ShouldQueue;

class AnnounceTierEndorsement implements ShouldQueue
{
    public function __construct(
        private readonly TrainingSuccessesAnnouncementService $announcementService
    ) {}

    public function handle(TierEndorsementAdded $event): void
    {
        $endorsement = $event->getEndorsement();

        if ($endorsement->endorsable instanceof PositionGroup) {
            $this->announcementService->announceTierEndorsement($event->getAccount(), $endorsement->endorsable);
        }
    }
}
