<?php

declare(strict_types=1);

namespace App\Jobs\Training;

use App\Enums\AvailabilityCheckStatus;
use App\Models\Cts\Session;
use App\Models\Training\TrainingPlace\AvailabilityCheck;
use App\Models\Training\TrainingPlace\AvailabilityWarning;
use App\Models\Training\TrainingPlace\TrainingPlace;
use App\Notifications\Training\AvailabilityWarningCreated;
use App\Services\Training\AvailabilityService;
use App\Services\Training\AvailabilityWarnings;
use App\Services\Training\TrainingPlaceService;
use Carbon\Carbon;
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
        // During leave of absence, record the check as on leave but do not send any warnings
        if ($this->trainingPlace->isOnLeaveOfAbsence()) {
            AvailabilityCheck::create([
                'training_place_id' => $this->trainingPlace->id,
                'status' => AvailabilityCheckStatus::OnLeave,
            ]);

            return;
        }

        if ($this->trainingPlace->isWithinAvailabilityCheckGracePeriod()) {
            return;
        }

        $account = $this->trainingPlace->account;

        if (! $account->member) {
            return;
        }

        $memberId = $account->member->id;

        // Check if availability exists for the student inside the future window
        $hasAvailability = app(AvailabilityService::class)->hasAvailabilityWithinWindow($memberId);

        $hasFutureBookedSession = $this->hasFutureBookedSession($memberId);

        $existingAvailabilityWarning = AvailabilityWarning::where('training_place_id', $this->trainingPlace->id)->where('status', 'pending')->first();

        $hasPendingExam = app(TrainingPlaceService::class)->hasPendingExam($this->trainingPlace);

        if ($this->trainingPlace->isPilot()) {
            $lastSession = $this->trainingPlace->lastCompletedSession();

            if ($lastSession && Carbon::parse($lastSession->taken_date)->addDays(7)->isFuture()) {
                $availabilityCheck = AvailabilityCheck::create([
                    'training_place_id' => $this->trainingPlace->id,
                    'status' => AvailabilityCheckStatus::Passed,
                ]);

                if ($existingAvailabilityWarning) {
                    AvailabilityWarnings::markWarningAsResolved($existingAvailabilityWarning, $availabilityCheck->id);
                }

                return;
            }
        }

        if ($hasPendingExam) {
            $availabilityCheck = AvailabilityCheck::create([
                'training_place_id' => $this->trainingPlace->id,
                'status' => AvailabilityCheckStatus::Passed,
            ]);

            if ($existingAvailabilityWarning) {
                AvailabilityWarnings::markWarningAsResolved($existingAvailabilityWarning, $availabilityCheck->id);
            }

            return;
        }

        // Availability or a future booked session is enough. Untaken session requests do not count.
        if ($hasAvailability || $hasFutureBookedSession) {
            $availabilityCheck = AvailabilityCheck::create([
                'training_place_id' => $this->trainingPlace->id,
                'status' => AvailabilityCheckStatus::Passed,
            ]);

            if ($existingAvailabilityWarning) {
                AvailabilityWarnings::markWarningAsResolved($existingAvailabilityWarning, $availabilityCheck->id);
            }

            return;
        }

        // Check failed - create failed check and warning
        $availabilityCheck = AvailabilityCheck::create([
            'training_place_id' => $this->trainingPlace->id,
            'status' => AvailabilityCheckStatus::Failed,
        ]);

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
                'expires_at' => now()->addDays($this->trainingPlace->availabilityWarningDays())->endOfDay(),
            ]);
            $account->notify(new AvailabilityWarningCreated($warning));
        });
    }

    /**
     * A booked session that has not been completed still counts as compliant,
     * even when the student has no open availability. Untaken requests are ignored.
     */
    private function hasFutureBookedSession(int $memberId): bool
    {
        $ctsPositions = $this->trainingPlace->trainableCtsPositions();

        if ($ctsPositions === []) {
            return false;
        }

        return Session::where('student_id', $memberId)
            ->whereIn('position', $ctsPositions)
            ->whereNotNull('taken_time')
            ->where('taken_date', '>=', now()->toDateString())
            ->where('session_done', 0)
            ->exists();
    }
}
