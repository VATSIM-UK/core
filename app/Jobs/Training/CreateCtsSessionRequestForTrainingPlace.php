<?php

declare(strict_types=1);

namespace App\Jobs\Training;

use App\Models\Cts\Position as CtsPosition;
use App\Models\Cts\Session as CtsSession;
use App\Models\Training\TrainingPlace\TrainingPlace;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class CreateCtsSessionRequestForTrainingPlace implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public TrainingPlace $trainingPlace) {}

    public function handle(): void
    {
        $this->trainingPlace->loadMissing([
            'trainingPosition',
            'waitingListAccount.account',
        ]);

        $account = $this->trainingPlace->waitingListAccount?->account;
        $member = $account?->member;

        if (! $member) {
            Log::warning('Skipping training place without CTS member attached', [
                'training_place_id' => (string) $this->trainingPlace->id,
            ]);

            return;
        }

        $callsign = $this->trainingPlace->trainingPosition?->cts_primary_position;

        if (! is_string($callsign) || trim($callsign) === '') {
            return;
        }

        $callsign = trim($callsign);
        $ctsPosition = CtsPosition::query()->where('callsign', $callsign)->first();

        if (! $ctsPosition) {
            Log::warning('CTS position not found for training place primary position', [
                'training_place_id' => (string) $this->trainingPlace->id,
                'cts_primary_position' => $callsign,
            ]);

            return;
        }

        $hasOpenSessionRequest = CtsSession::query()
            ->where('student_id', $member->id)
            ->where('position', $callsign)
            ->whereNull('taken_date')
            ->exists();

        // Check if the student has a session booked that has not been completed
        // and might be in the future
        $hasSessionBooked = CtsSession::query()
            ->where('student_id', $member->id)
            ->where('position', $callsign)
            ->whereNotNull('taken_date')
            ->where('session_done', 0)
            ->where('taken_date', '>=', now()->toDateString())
            ->exists();

        if ($hasOpenSessionRequest || $hasSessionBooked) {
            return;
        }

        CtsSession::query()->create([
            'rts_id' => $ctsPosition->rts_id,
            'position' => $ctsPosition->callsign,
            'progress_sheet_id' => $ctsPosition->prog_sheet_id ?? 0,
            'student_id' => $member->id,
            'student_rating' => $member->rating ?? 0,
            'request_time' => now(),
        ]);
    }
}
