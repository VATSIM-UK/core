<?php

namespace App\Services\Training;

use App\Enums\PositionValidationStatusEnum;
use App\Models\Cts\Position;
use App\Models\Cts\PositionValidation;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Models\Training\TrainingPosition\TrainingPosition;
use App\Models\Training\WaitingList\Removal;
use App\Models\Training\WaitingList\RemovalReason;
use App\Models\Training\WaitingList\WaitingListAccount;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class TrainingPlaceService
{
    public function assignMentoringPermissions(TrainingPlace $trainingPlace): void
    {
        $student = $trainingPlace->waitingListAccount->account;

        if (! $student->member) {
            Log::error('Student does not have a CTS member model attached');

            return;
        }

        $ctsPositions = $trainingPlace->trainingPosition->cts_positions;

        foreach ($ctsPositions as $ctsPosition) {
            $ctsPositionModel = Position::where('callsign', $ctsPosition)->first();

            if (! $ctsPositionModel) {
                Log::error("CTS position with callsign {$ctsPosition} not found");

                continue;
            }

            // Check if the validation already exists to prevent duplicates
            $existingValidation = PositionValidation::where('member_id', $student->member->id)
                ->where('position_id', $ctsPositionModel->id)
                ->where('status', PositionValidationStatusEnum::Student->value)
                ->first();

            if ($existingValidation) {
                continue;
            }

            PositionValidation::create([
                // use CTS member id
                'member_id' => $student->member->id,
                'position_id' => $ctsPositionModel->id,
                'status' => PositionValidationStatusEnum::Student->value,
                'changed_by' => $student->id,
                'date_changed' => now(),
            ]);
        }
    }

    public function revokeMentoringPermissions(TrainingPlace $trainingPlace): void
    {
        $student = $trainingPlace->waitingListAccount->account;

        if (! $student->member) {
            Log::error('Student does not have a CTS member model attached');

            return;
        }

        $ctsPositions = $trainingPlace->trainingPosition->cts_positions;

        foreach ($ctsPositions as $ctsPosition) {
            $ctsPositionModel = Position::where('callsign', $ctsPosition)->first();

            if (! $ctsPositionModel) {
                Log::error("CTS position with callsign {$ctsPosition} not found");

                continue;
            }

            PositionValidation::where('member_id', $student->member->id)
                ->where('position_id', $ctsPositionModel->id)
                ->where('status', PositionValidationStatusEnum::Student->value)
                ->delete();
        }
    }

    public function createManualTrainingPlace(WaitingListAccount $waitingListAccount, TrainingPosition $trainingPosition): TrainingPlace
    {
        $trainingPlace = TrainingPlace::create([
            'waiting_list_account_id' => $waitingListAccount->id,
            'training_position_id' => $trainingPosition->id,
        ]);

        $this->removeFromWaitingList($trainingPlace);

        return $trainingPlace;
    }

    public function removeFromWaitingList(TrainingPlace $trainingPlace): void
    {
        if (! $trainingPlace->waitingListAccount) {
            return;
        }

        $removal = new Removal(RemovalReason::TrainingPlace, Auth::user()->id);

        $trainingPlace->waitingListAccount->waitingList->removeFromWaitingList($trainingPlace->waitingListAccount->account, $removal);
    }
}
