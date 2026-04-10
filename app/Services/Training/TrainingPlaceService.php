<?php

namespace App\Services\Training;

use App\Enums\PositionValidationStatusEnum;
use App\Models\Cts\ExamBooking;
use App\Models\Cts\Position;
use App\Models\Cts\PositionValidation;
use App\Models\Mship\Account;
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
        $student = $trainingPlace->studentAccount();

        if (! $student) {
            Log::error('Training place does not have an account associated');

            return;
        }

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
        $student = $trainingPlace->studentAccount();

        if (! $student) {
            Log::error('Training place does not have an account associated');

            return;
        }

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
            'account_id' => $waitingListAccount->account_id,
            'training_position_id' => $trainingPosition->id,
        ]);

        $this->removeFromWaitingList($trainingPlace);

        return $trainingPlace;
    }

    public function createAdhocTrainingPlace(Account $account, TrainingPosition $trainingPosition): TrainingPlace
    {
        return TrainingPlace::create([
            'account_id' => $account->id,
            'training_position_id' => $trainingPosition->id,
            'waiting_list_account_id' => null,
        ]);
    }

    public function removeFromWaitingList(TrainingPlace $trainingPlace): void
    {
        if (! $trainingPlace->waitingListAccount) {
            return;
        }

        $removal = new Removal(RemovalReason::TrainingPlace, Auth::user()->id);

        $trainingPlace->waitingListAccount->waitingList->removeFromWaitingList($trainingPlace->waitingListAccount->account, $removal);
    }

    public function hasPendingExam(TrainingPlace $trainingPlace): bool
    {
        $student = $trainingPlace->studentAccount();

        if (! $student?->member) {
            Log::error('Student does not have a CTS member model attached');

            return false;
        }

        $trainingPosition = $trainingPlace->trainingPosition;

        if (! $trainingPosition) {
            Log::error('Training position not found');

            return false;
        }

        $examPosition = $trainingPosition->exam_callsign
            ?? $trainingPosition->position?->callsign
            ?? null;

        if (! $examPosition) {
            Log::error('Exam position not found');

            return false;
        }

        return ExamBooking::where('student_id', $student->member->id)
            ->where('position_1', $examPosition)
            ->where('finished', ExamBooking::NOT_FINISHED_FLAG)
            ->exists();
    }
}
