<?php

namespace App\Observers;

use App\Events\Mship\Endorsement\PositionEndorsementAdded;
use App\Events\Mship\Endorsement\TierEndorsementAdded;
use App\Jobs\Mship\SyncToDiscord;
use App\Models\Atc\Position;
use App\Models\Atc\PositionGroup;
use App\Models\Mship\Account\Endorsement;
use App\Models\Mship\State;

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

        // Position endorsements can be granted to visitors, so this is targeting
        // specifically home members
        $accountIsHomeMember = $endorsement->account->hasState(State::findByCode('DIVISION'));
        if ($endorsement->endorsable_type == Position::class && $accountIsHomeMember && $endorsement->expires()) {
            event(new PositionEndorsementAdded($endorsement, $endorsement->account));
        }

        $this->syncSoloEndorsementToDiscord($endorsement);
    }

    /**
     * Handle the Endorsement "updated" event.
     */
    public function updated(Endorsement $endorsement): void
    {
        $endorsement->load('account');

        $this->syncSoloEndorsementToDiscord($endorsement);
    }

    /**
     * Handle the Endorsement "deleted" event.
     */
    public function deleted(Endorsement $endorsement): void
    {
        $endorsement->load('account');

        $this->syncSoloEndorsementToDiscord($endorsement);
    }

    /**
     * Handle the Endorsement "restored" event.
     */
    public function restored(Endorsement $endorsement): void
    {
        $endorsement->load('account');

        $this->syncSoloEndorsementToDiscord($endorsement);
    }

    /**
     * Handle the Endorsement "force deleted" event.
     */
    public function forceDeleted(Endorsement $endorsement): void
    {
        $endorsement->load('account');

        $this->syncSoloEndorsementToDiscord($endorsement);
    }

    /**
     * Dispatch a Discord sync for solo endorsements
     */
    private function syncSoloEndorsementToDiscord(Endorsement $endorsement): void
    {
        if ($endorsement->endorsable_type !== Position::class) {
            return;
        }

        if ($endorsement->account && $endorsement->account->discord_id) {
            SyncToDiscord::dispatch($endorsement->account);
        }
    }
}
