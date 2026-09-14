<?php

declare(strict_types=1);

namespace App\Jobs\Training;

use App\Models\Training\TrainingPlace\AvailabilityWarning;
use App\Notifications\Training\TrainingPlaceRemovedDueToExpiredAvailability;
use App\Services\Training\AvailabilityWarnings;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ActionExpiredAvailabilityWarningRemoval implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(public AvailabilityWarning $availabilityWarning) {}

    public function handle(): void
    {
        $this->availabilityWarning->refresh();

        if ($this->availabilityWarning->status !== 'pending') {
            Log::info('Availability warning is no longer pending, skipping', ['availability_warning_id' => $this->availabilityWarning->id]);

            return;
        }

        if ($this->availabilityWarning->expires_at->isFuture()) {
            Log::info('Availability warning has not yet expired, skipping', ['availability_warning_id' => $this->availabilityWarning->id]);

            return;
        }

        $trainingPlace = $this->availabilityWarning->trainingPlace;

        if (! $trainingPlace) {
            Log::warning('Training place not found for availability warning. Cannot process removal', ['availability_warning_id' => $this->availabilityWarning->id]);

            return;
        }

        $account = $trainingPlace->account;

        try {
            DB::transaction(function () use ($trainingPlace, $account): void {
                $trainingPlace->delete();
                AvailabilityWarnings::markWarningAsExpired($this->availabilityWarning);
                $account->notify(new TrainingPlaceRemovedDueToExpiredAvailability($this->availabilityWarning));
            });
        } catch (Exception $e) {
            Log::error('Failed to process expired availability warning. Will be retried on the next run', [
                'availability_warning_id' => $this->availabilityWarning->id,
                'exception' => $e,
            ]);
            $this->fail($e);

            return;
        }

        Log::info('Training place removed due to expired availability warning, account notified', [
            'training_place_id' => $trainingPlace->id,
            'availability_warning_id' => $this->availabilityWarning->id,
            'account_id' => $account->id,
        ]);
    }
}
