<?php

declare(strict_types=1);

namespace App\Console\Commands\Training;

use App\Enums\PositionValidationStatusEnum;
use App\Models\Cts\PositionValidation;
use App\Models\Training\Mentoring\MentorTrainingPosition;
use App\Models\Training\TrainingPosition\TrainingPosition;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

// This command exists soely to sync mentor permissions from the legacy CTS system to the new system.
// Currently changes in core ARE reflected in the CTS
// HOWEVER, changes in the CTS are NOT automatically reflected in core
// To ensure the 2 systems remain in sync, this job will be run on a regular schedule.
// ONCE the permission management is deprechated in the CTS and fully transitioned to the new system, this job can be removed.
class SyncCtsMentorValidations extends Command
{
    protected $signature = 'sync:cts-mentor-validations';

    protected $description = 'Syncs mentor position validations from the legacy CTS system to the new mentoring tables.';

    public function handle(): int
    {
        $this->info('Starting CTS Mentor Validation sync...');

        $validations = PositionValidation::with('position')
            ->where('status', PositionValidationStatusEnum::Mentor->value)
            ->get();

        $validationsByMember = $validations->groupBy('member_id');
        $trainingPositions = TrainingPosition::all();

        $managedTrainingPositionIds = $trainingPositions
            ->filter(fn (TrainingPosition $tp) => ! empty($tp->cts_positions))
            ->pluck('id')
            ->toArray();

        DB::transaction(function () use ($validationsByMember, $trainingPositions, $managedTrainingPositionIds): void {
            $processedAccountIds = [];

            foreach ($validationsByMember as $memberId => $memberValidations) {
                $processedAccountIds[] = $memberId;

                $ctsCallsigns = $memberValidations
                    ->map(fn ($validation) => $validation->position?->callsign)
                    ->filter()
                    ->toArray();

                $matchingTrainingPositionIds = $trainingPositions->filter(function (TrainingPosition $tp) use ($ctsCallsigns) {
                    $tpCtsPositions = is_array($tp->cts_positions) ? $tp->cts_positions : collect($tp->cts_positions)->toArray();

                    if (empty($tpCtsPositions)) {
                        return false;
                    }

                    return count(array_intersect($tpCtsPositions, $ctsCallsigns)) > 0;
                })->pluck('id')->toArray();

                $currentTrainingPositionIds = MentorTrainingPosition::where('account_id', $memberId)
                    ->whereIn('training_position_id', $managedTrainingPositionIds)
                    ->pluck('training_position_id')
                    ->toArray();

                $toAdd = array_diff($matchingTrainingPositionIds, $currentTrainingPositionIds);
                $toRemove = array_diff($currentTrainingPositionIds, $matchingTrainingPositionIds);

                if (! empty($toAdd)) {
                    $inserts = array_map(fn ($tpId) => [
                        'account_id' => $memberId,
                        'training_position_id' => $tpId,
                        'created_by' => $memberId,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ], $toAdd);

                    MentorTrainingPosition::insert($inserts);
                }

                if (! empty($toRemove)) {
                    MentorTrainingPosition::where('account_id', $memberId)
                        ->whereIn('training_position_id', $toRemove)
                        ->delete();
                }
            }

            MentorTrainingPosition::whereNotIn('account_id', $processedAccountIds)
                ->whereIn('training_position_id', $managedTrainingPositionIds)
                ->delete();
        });

        $this->info('Successfully synchronized CTS Mentor Validations.');

        return Command::SUCCESS;
    }
}
