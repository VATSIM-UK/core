<?php

namespace App\Observers;

use App\Events\Mship\Endorsement\TierEndorsementAdded;
use App\Models\Atc\PositionGroup;
use App\Models\Mship\Account\Endorsement;

class EndorsementObserver
{
    /**
     * Handle the Endorsement "created" event.
     */
    public function created(Endorsement $endorsement): void
    {
        $endorsement->load('account');

        if ($endorsement->endorsable_type == PositionGroup::class) {
            event(new TierEndorsementAdded($endorsement, $endorsement->account));

            return;
        }
    }

    /**
     * Handle the Endorsement "updated" event.
     */
    public function updated(Endorsement $endorsement): void
    {
        //
    }

    /**
     * Handle the Endorsement "deleted" event.
     */
    public function deleted(Endorsement $endorsement): void
    {
        //
    }

    /**
     * Handle the Endorsement "restored" event.
     */
    public function restored(Endorsement $endorsement): void
    {
        //
    }

    /**
     * Handle the Endorsement "force deleted" event.
     */
    public function forceDeleted(Endorsement $endorsement): void
    {
        //
    }
}
