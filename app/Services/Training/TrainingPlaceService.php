<?php

namespace App\Services\Training;

use App\Enums\PositionValidationStatusEnum;
use App\Models\Cts\ExamBooking;
use App\Models\Cts\Membership;
use App\Models\Cts\Position;
use App\Models\Cts\PositionValidation;
use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
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
        $student = $trainingPlace->account;

        if (! $student->member) {
            Log::error('Student does not have a CTS member model attached', [
                'account_id' => $student->id,
                'training_place_id' => $trainingPlace->id,
            ]);

            return;
        }

        $ctsPositions = $trainingPlace->trainableCtsPositions();

        foreach ($ctsPositions as $ctsPosition) {
            $ctsPositionModel = Position::where('callsign', $ctsPosition)->first();

            if (! $ctsPositionModel) {
                Log::error('CTS position not found', [
                    'callsign' => $ctsPosition,
                    'account_id' => $student->id,
                    'training_place_id' => $trainingPlace->id,
                ]);

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

            Log::channel('audit')->info('CTS position validation granted', [
                'account_id' => $student->id,
                'member_id' => $student->member->id,
                'position_id' => $ctsPositionModel->id,
                'callsign' => $ctsPosition,
                'training_place_id' => $trainingPlace->id,
            ]);
        }
    }

    public function revokeMentoringPermissions(TrainingPlace $trainingPlace): void
    {
        $student = $trainingPlace->account;

        if (! $student->member) {
            Log::error('Student does not have a CTS member model attached', [
                'account_id' => $student->id,
                'training_place_id' => $trainingPlace->id,
            ]);

            return;
        }

        $ctsPositions = $trainingPlace->trainableCtsPositions();

        foreach ($ctsPositions as $ctsPosition) {
            $ctsPositionModel = Position::where('callsign', $ctsPosition)->first();

            if (! $ctsPositionModel) {
                Log::error('CTS position not found', [
                    'callsign' => $ctsPosition,
                    'account_id' => $student->id,
                    'training_place_id' => $trainingPlace->id,
                ]);

                continue;
            }

            PositionValidation::where('member_id', $student->member->id)
                ->where('position_id', $ctsPositionModel->id)
                ->where('status', PositionValidationStatusEnum::Student->value)
                ->delete();

            Log::channel('audit')->info('CTS position validation revoked', [
                'account_id' => $student->id,
                'member_id' => $student->member->id,
                'position_id' => $ctsPositionModel->id,
                'callsign' => $ctsPosition,
                'training_place_id' => $trainingPlace->id,
            ]);
        }
    }

    public function removeCtsMembershipsForTrainingPlace(TrainingPlace $trainingPlace): void
    {
        $trainingPlace->loadMissing(['trainable', 'account']);

        $student = $trainingPlace->account;

        if (! $student->member) {
            Log::error('Student does not have a CTS member model attached', [
                'account_id' => $student->id,
                'training_place_id' => $trainingPlace->id,
            ]);

            return;
        }

        $ctsPositions = $trainingPlace->trainableCtsPositions();

        if ($ctsPositions === []) {
            return;
        }

        $rtsIds = [];

        foreach ($ctsPositions as $ctsPositionCallsign) {
            $ctsPositionModel = Position::where('callsign', $ctsPositionCallsign)->first();

            if (! $ctsPositionModel) {
                Log::error('CTS position not found', [
                    'callsign' => $ctsPositionCallsign,
                    'account_id' => $student->id,
                    'training_place_id' => $trainingPlace->id,
                ]);

                continue;
            }

            $rtsId = (int) $ctsPositionModel->rts_id;

            if ($rtsId !== 0) {
                $rtsIds[$rtsId] = true;
            }
        }

        if ($rtsIds === []) {
            return;
        }

        $deleted = Membership::query()
            ->where('member_id', $student->member->id)
            ->whereIn('rts_id', array_keys($rtsIds))
            ->whereIn('type', ['H', 'V'])
            ->delete();

        if ($deleted > 0) {
            Log::channel('audit')->info('CTS membership revoked', [
                'account_id' => $student->id,
                'member_id' => $student->member->id,
                'rts_ids' => array_keys($rtsIds),
                'training_place_id' => $trainingPlace->id,
            ]);
        }
    }

    public function createManualTrainingPlace(WaitingListAccount $waitingListAccount, TrainingPosition|Qualification $trainable): TrainingPlace
    {
        $trainingPlace = TrainingPlace::create([
            'waiting_list_account_id' => $waitingListAccount->id,
            'account_id' => $waitingListAccount->account_id,
            'trainable_type' => $trainable->getMorphClass(),
            'trainable_id' => $trainable->getKey(),
        ]);

        Log::channel('audit')->info('Manual training place created', [
            'account_id' => $waitingListAccount->account_id,
            'training_place_id' => $trainingPlace->id,
            'waiting_list_account_id' => $waitingListAccount->id,
            'trainable_type' => $trainable->getMorphClass(),
            'trainable_id' => $trainable->getKey(),
        ]);

        $this->removeFromWaitingList($trainingPlace);

        return $trainingPlace;
    }

    public function createAdhocTrainingPlace(
        Account $account,
        TrainingPosition|Qualification $trainable,
        string $reason,
        Account $actor,
    ): TrainingPlace {
        if ($trainable instanceof TrainingPosition) {
            $trainable->loadMissing('position');
        }

        $trainingPlace = TrainingPlace::create([
            'account_id' => $account->id,
            'trainable_type' => $trainable->getMorphClass(),
            'trainable_id' => $trainable->getKey(),
            'waiting_list_account_id' => null,
        ]);

        Log::channel('audit')->info('Adhoc training place created', [
            'account_id' => $account->id,
            'training_place_id' => $trainingPlace->id,
            'trainable_type' => $trainable->getMorphClass(),
            'trainable_id' => $trainable->getKey(),
            'actor_id' => $actor->id,
            'reason' => preg_replace('/[\x00-\x1F\x7F]/', '', $reason),
        ]);

        $account->addNote(
            'training',
            "Ad-hoc training place created on {$trainingPlace->display_name} outside the usual waiting list flow. Reason: {$reason}",
            $actor->id,
        );

        return $trainingPlace;
    }

    public function removeFromWaitingList(TrainingPlace $trainingPlace): void
    {
        if (! $trainingPlace->waitingListAccount) {
            return;
        }

        $removal = new Removal(RemovalReason::TrainingPlace, Auth::user()->id);

        $trainingPlace->waitingListAccount->waitingList->removeFromWaitingList($trainingPlace->account, $removal);
    }

    public function hasPendingExam(TrainingPlace $trainingPlace): bool
    {
        $student = $trainingPlace->account;

        if (! $student->member) {
            Log::error('Student does not have a CTS member model attached', [
                'account_id' => $student->id,
                'training_place_id' => $trainingPlace->id,
            ]);

            return false;
        }

        $trainingPlace->unsetRelation('trainable');
        $trainingPosition = $trainingPlace->trainingPosition;

        if (! $trainingPosition) {
            Log::error('Training position not found', [
                'account_id' => $student->id,
                'training_place_id' => $trainingPlace->id,
            ]);

            return false;
        }

        $trainingPosition->loadMissing('position');

        $examPosition = $trainingPosition->exam_callsign
            ?? $trainingPosition->position?->callsign
            ?? null;

        if (! $examPosition) {
            Log::error('Exam position not found', [
                'account_id' => $student->id,
                'training_place_id' => $trainingPlace->id,
                'training_position_id' => $trainingPosition->id,
            ]);

            return false;
        }

        return ExamBooking::where('student_id', $student->member->id)
            ->where('position_1', $examPosition)
            ->where('finished', ExamBooking::NOT_FINISHED_FLAG)
            ->exists();
    }
}
