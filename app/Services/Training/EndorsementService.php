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

    public static function getAllSoloEndorsementsIncludingRelatedPositionsForTrainingPlace(TrainingPlace $trainingPlace): Builder
    {
        $position = $trainingPlace->trainingPosition->position;
        $callsignParts = explode('_', $position->callsign);
        $suffix = end($callsignParts);

        // Get all positions with the same suffix (including the direct position)
        $positionIds = Position::where('callsign', 'LIKE', '%_'.$suffix)
            ->pluck('id');

        return Endorsement::query()
            // use a computed column to group the endorsements by the position they are related to
            // so we can use these in a grouping within the table
            ->selectRaw(
                'mship_account_endorsement.*,
                CASE
                    WHEN endorsable_id = ? THEN "Training Place Position"
                    ELSE "Related Position by Rating"
                END as endorsement_category',
                [$position->id]
            )
            ->where('account_id', $trainingPlace->waitingListAccount->account_id)
            ->whereIn('endorsable_id', $positionIds)
            ->where('endorsable_type', Position::class)
            ->whereNotNull('expires_at')
            // ensure the training place position is always first in the query.
            ->orderByRaw('CASE WHEN endorsable_id = ? THEN 0 ELSE 1 END', [$position->id]);
    }
}
