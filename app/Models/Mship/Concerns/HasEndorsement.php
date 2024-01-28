<?php

namespace App\Models\Mship\Concerns;

use App\Models\Mship\Account\Endorsement;

trait HasEndorsement
{
    public function endorsements()
    {
        return $this->hasMany(Endorsement::class, 'account_id');
    }

    public function permanentEndorsements()
    {
        return $this->endorsements()->whereNull('expired_at');
    }

    public function temporaryEndorsements()
    {
        return $this->endorsements()->whereNotNull('expired_at');
    }
}
