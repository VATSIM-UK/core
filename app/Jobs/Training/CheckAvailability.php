<?php

namespace App\Jobs\Training;

use App\Models\Cts\Availability;
use App\Models\Training\TrainingPlace\AvailabilityCheck;
use App\Models\Training\TrainingPlace\AvailabilityWarning;
use App\Models\Training\TrainingPlace\TrainingPlace;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class CheckAvailability implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public TrainingPlace $trainingPlace)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $account = $this->trainingPlace->waitingListAccount->account;

        $availability = Availability::where('student_id', $account->member->id)->get();

        if ($availability->count() > 0) {
            AvailabilityCheck::create([
                'training_place_id' => $this->trainingPlace->id,
                'status' => 'passed',
            ]);

            return;
        }

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

        AvailabilityWarning::create([
            'training_place_id' => $this->trainingPlace->id,
            'availability_check_id' => $availabilityCheck->id,
            'status' => 'pending',
            'expires_at' => now()->addDays(5),
        ]);
    }
}
