<?php

namespace App\Services\Training;

use App\Models\Atc\Endorseable;
use App\Models\Atc\Position;
use App\Models\Mship\Account;
use App\Models\Mship\Account\Endorsement;
use App\Models\Training\TrainingPlace\TrainingPlace;
use Illuminate\Database\Eloquent\Builder;

class EndorsementService
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

    public static function getSoloEndorsementsForTrainingPlace(TrainingPlace $trainingPlace): Builder
    {
        return Endorsement::query()
            ->where('account_id', $trainingPlace->waitingListAccount->account_id)
            ->where('endorsable_id', $trainingPlace->trainingPosition->position_id)
            ->where('endorsable_type', Position::class)
            ->whereNotNull('expires_at')
            ->where('created_at', '>=', $trainingPlace->created_at);
    }
}
