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
            Log::info("Availability warning {$this->availabilityWarning->id} is no longer pending, skipping.");

            return;
        }

        if ($this->availabilityWarning->expires_at->isFuture()) {
            Log::info("Availability warning {$this->availabilityWarning->id} has not yet expired, skipping.");

            return;
        }

        $trainingPlace = $this->availabilityWarning->trainingPlace;

        if (! $trainingPlace) {
            Log::warning("Training place not found for availability warning {$this->availabilityWarning->id}. Cannot process removal.");

            return;
        }

        $account = $trainingPlace->waitingListAccount->account;

        try {
            DB::transaction(function () use ($trainingPlace, $account): void {
                $trainingPlace->delete();
                AvailabilityWarnings::markWarningAsExpired($this->availabilityWarning);
                $account->notify(new TrainingPlaceRemovedDueToExpiredAvailability($this->availabilityWarning));
            });
        } catch (Exception $e) {
            Log::error("Failed to process expired availability warning {$this->availabilityWarning->id}: {$e->getMessage()}. Will be retried on the next run.");
            $this->fail($e);

            return;
        }

        Log::info("Training place {$trainingPlace->id} removed due to expired availability warning {$this->availabilityWarning->id}. Account {$account->id} notified.");
    }
}
