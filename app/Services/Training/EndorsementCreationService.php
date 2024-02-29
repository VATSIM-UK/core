<?php

namespace App\Services\Training;

use App\Models\Atc\Endorseable;
use App\Models\Mship\Account;
use App\Models\Mship\Account\Endorsement;

class EndorsementCreationService
{
    public static function createPermanent(Endorseable $endorsable, Account $account, Account $creator)
    {
        return Endorsement::create([
            'account_id' => $account->id,
            'created_by' => $creator->id,
            'endorsable_type' => $endorsable::class,
            'endorsable_id' => $endorsable->id,
            'expires_at' => null,
            'endorsement_request_id' => null,
        ]);
    }
}
