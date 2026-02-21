<?php

declare(strict_types=1);

namespace App\Jobs\Training;

use App\Models\Cts\Availability;
use App\Models\Cts\Session;
use App\Models\Training\TrainingPlace\AvailabilityCheck;
use App\Models\Training\TrainingPlace\AvailabilityWarning;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Notifications\Training\AvailabilityWarningCreated;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\DB;

class CheckAvailability implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public TrainingPlace $trainingPlace) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $account = $this->trainingPlace->waitingListAccount->account;
        $memberId = $account->member->id;

        // Check if availability exists for the student
        $availability = Availability::where('student_id', $memberId)->get();
        $hasAvailability = $availability->count() > 0;

        // Check if a session request exists for the training position
        $hasSessionRequest = $this->checkSessionRequest($memberId);

        // Check passes only if BOTH availability and session request exist
        if ($hasAvailability && $hasSessionRequest) {
            AvailabilityCheck::create([
                'training_place_id' => $this->trainingPlace->id,
                'status' => 'passed',
            ]);

            return;
        }

        // Check failed - create failed check and warning
        $availabilityCheck = AvailabilityCheck::create([
            'training_place_id' => $this->trainingPlace->id,
            'status' => 'failed',
        ]);

        $existingAvailabilityWarning = AvailabilityWarning::where('training_place_id', $this->trainingPlace->id)->where('status', 'pending')->first();

        if ($existingAvailabilityWarning) {
            // There is already a pending availability warning for this training place
            // We don't need to create a new one
            return;
        }

        // Fourth failure: they have already had 3 instances where they failed then resolved within the window
        $resolvedWarningCount = AvailabilityWarning::where('training_place_id', $this->trainingPlace->id)
            ->where('status', 'resolved')
            ->count();

        if ($resolvedWarningCount >= 3) {
            $warning = AvailabilityWarning::create([
                'training_place_id' => $this->trainingPlace->id,
                'availability_check_id' => $availabilityCheck->id,
                'status' => 'pending',
                'expires_at' => now(),
            ]);
            ActionFourthAvailabilityFailureRemoval::dispatch($warning);

            return;
        }

        DB::transaction(function () use ($account, $availabilityCheck): void {
            $warning = AvailabilityWarning::create([
                'training_place_id' => $this->trainingPlace->id,
                'availability_check_id' => $availabilityCheck->id,
                'status' => 'pending',
                'expires_at' => now()->addDays(5)->endOfDay(),
            ]);
            $account->notify(new AvailabilityWarningCreated($warning));
        });
    }

    /**
     * Check if a session request exists for the student with a position matching
     * any of the callsigns defined in the training position's cts_positions.
     */
    private function checkSessionRequest(int $memberId): bool
    {
        // Get the training position's callsigns
        $trainingPosition = $this->trainingPlace->trainingPosition;

        if (! $trainingPosition || ! $trainingPosition->cts_positions) {
            return false;
        }

        // Check if a session exists for this student with a matching callsign
        $sessionExists = Session::where('student_id', $memberId)
            ->whereIn('position', $trainingPosition->cts_positions)
            ->exists();

        return $sessionExists;
    }
}
