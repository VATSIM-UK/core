<?php

namespace App\Models\Mship\Concerns;

use App\Models\Atc\Position;
use App\Models\Atc\PositionGroup;
use App\Models\Mship\Account\Endorsement;

trait HasEndorsement
{
    public function endorsements()
    {
        return $this->hasMany(Endorsement::class, 'account_id');
    }

    public function permanentEndorsements()
    {
        return $this->endorsements()->whereNull('expires_at');
    }

    public function temporaryEndorsements()
    {
        return $this->endorsements()->whereNotNull('expires_at');
    }

    /**
     * Whether every active endorsement the account holds is for the given position
     * group, either as a group endorsement or as a position within that group.
     */
    public function onlyHoldsEndorsementsForPositionGroup(PositionGroup $positionGroup): bool
    {
        $endorsements = $this->endorsements->filter(fn (Endorsement $endorsement) => ! $endorsement->hasExpired());

        if ($endorsements->isEmpty()) {
            return false;
        }

        $positionIds = $positionGroup->positions->pluck('id');

        return $endorsements->every(fn (Endorsement $endorsement) => match ($endorsement->endorsable_type) {
            PositionGroup::class => (int) $endorsement->endorsable_id === (int) $positionGroup->id,
            Position::class => $positionIds->contains((int) $endorsement->endorsable_id),
            default => false,
        });
    }

    public function daysSpentTemporarilyEndorsedOn(Position $endorsable): int
    {
        $positionTemporaryEndorsements = $this->temporaryEndorsements()
            ->where('endorsable_id', $endorsable->id)
            ->where('endorsable_type', Position::class)
            ->get();

        $daysFromExpiredEndorsements = $positionTemporaryEndorsements
            ->where('expires_at', '<', now())
            ->sum(fn ($endorsement) => $endorsement->created_at->startOfDay()->diffInDays($endorsement->expires_at));

        $daysFromActiveEndorsements = $positionTemporaryEndorsements
            ->where('expires_at', '>=', now())
            ->sum(fn ($endorsement) => $endorsement->created_at->startOfDay()->diffInDays(now()) + 1);

        return $daysFromExpiredEndorsements + $daysFromActiveEndorsements;
    }
}
