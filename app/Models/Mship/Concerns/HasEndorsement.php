<?php

namespace App\Models\Mship\Concerns;

use App\Models\Atc\Position;
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

    public function daysSpentTemporarilyEndorsedOn(Position $endorsable): int
    {
        $positionTemporaryEndorsements = $this->temporaryEndorsements()
            ->where('endorsable_id', $endorsable->id)
            ->where('endorsable_type', Position::class)
            ->get();

        $daysFromExpiredEndorsements = $positionTemporaryEndorsements
            ->where('expired_at', '<', now())
            ->sum(fn ($endorsement) => $endorsement->created_at->diffInDays($endorsement->expired_at));

        $daysFromActiveEndorsements = $positionTemporaryEndorsements
            ->where('expired_at', '>=', now())
            ->sum(fn ($endorsement) => $endorsement->created_at->diffInDays(now()) + 1);

        return $daysFromExpiredEndorsements + $daysFromActiveEndorsements;
    }
}
