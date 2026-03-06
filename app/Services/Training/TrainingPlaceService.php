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
            $ctsPositionModel = $this->findCtsPosition($ctsPosition);

            if (! $ctsPositionModel) {
                continue;
            }

            if ($this->studentValidationExists((int) $student->member->id, (int) $ctsPositionModel->id)) {
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
            $ctsPositionModel = $this->findCtsPosition($ctsPosition);

            if (! $ctsPositionModel) {
                continue;
            }

            PositionValidation::where('member_id', $student->member->id)
                ->where('position_id', $ctsPositionModel->id)
                ->where('status', PositionValidationStatusEnum::Student->value)
                ->delete();
        }
    }

    private function findCtsPosition(string $callsign): ?Position
    {
        $position = Position::where('callsign', $callsign)->first();

        if (! $position) {
            Log::error("CTS position with callsign {$callsign} not found");

            return null;
        }

        return $position;
    }

    private function studentValidationExists(int $memberId, int $positionId): bool
    {
        return PositionValidation::where('member_id', $memberId)
            ->where('position_id', $positionId)
            ->where('status', PositionValidationStatusEnum::Student->value)
            ->exists();
    }

    public function createManualTrainingPlace(WaitingListAccount $waitingListAccount, TrainingPosition $trainingPosition, ?int $actorId = null): TrainingPlace
    {
        $trainingPlace = TrainingPlace::create([
            'waiting_list_account_id' => $waitingListAccount->id,
            'training_position_id' => $trainingPosition->id,
        ]);

        $this->removeFromWaitingList($trainingPlace, $actorId);

        return $trainingPlace;
    }

    public function removeFromWaitingList(TrainingPlace $trainingPlace, ?int $actorId = null): void
    {
        if (! $trainingPlace->waitingListAccount) {
            return;
        }

        $removal = new Removal(RemovalReason::TrainingPlace, $actorId);

        $trainingPlace->waitingListAccount->waitingList->removeFromWaitingList($trainingPlace->waitingListAccount->account, $removal);
    }
}
