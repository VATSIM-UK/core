<?php

declare(strict_types=1);

namespace App\Services\Training;

use App\Enums\AvailabilityLogEvent;
use App\Models\Training\TrainingPlace\AvailabilityLogEntry;
use App\Models\Training\TrainingPlace\TrainingPlace;
use Carbon\Carbon;
use Closure;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class AvailabilityLogService
{
    /**
     * @return Collection<int, TrainingPlace>
     */
    public function activeTrainingPlacesForAccount(int $cid): Collection
    {
        return TrainingPlace::query()
            ->where('account_id', $cid)
            ->whereNull('deleted_at')
            ->get();
    }

    /**
     * @param  Collection<int, TrainingPlace>  $places
     */
    public function recordAdded(Collection $places, Carbon $from, Carbon $to): void
    {
        foreach ($places as $place) {
            $this->write(fn () => AvailabilityLogEntry::create([
                'training_place_id' => $place->id,
                'event' => AvailabilityLogEvent::Added,
                'slot_from' => $from,
                'slot_to' => $to,
                'created_at' => now(),
            ]));
        }
    }

    /**
     * @param  Collection<int, TrainingPlace>  $places
     */
    public function recordMerged(Collection $places, Carbon $oldFrom, Carbon $oldTo, Carbon $newFrom, Carbon $newTo): void
    {
        $now = now();

        foreach ($places as $place) {
            $this->write(function () use ($place, $now, $oldFrom, $oldTo, $newFrom, $newTo): void {
                DB::transaction(function () use ($place, $now, $oldFrom, $oldTo, $newFrom, $newTo): void {
                    $this->supersedeMatching($place, $oldFrom, $oldTo, $now);

                    AvailabilityLogEntry::create([
                        'training_place_id' => $place->id,
                        'event' => AvailabilityLogEvent::Merged,
                        'slot_from' => $newFrom,
                        'slot_to' => $newTo,
                        'created_at' => $now,
                    ]);
                });
            });
        }
    }

    /**
     * @param  Collection<int, TrainingPlace>  $places
     */
    public function recordEdited(Collection $places, Carbon $oldFrom, Carbon $oldTo, Carbon $newFrom, Carbon $newTo): void
    {
        $now = now();

        foreach ($places as $place) {
            $this->write(function () use ($place, $now, $oldFrom, $oldTo, $newFrom, $newTo): void {
                DB::transaction(function () use ($place, $now, $oldFrom, $oldTo, $newFrom, $newTo): void {
                    $this->supersedeMatching($place, $oldFrom, $oldTo, $now);

                    AvailabilityLogEntry::create([
                        'training_place_id' => $place->id,
                        'event' => AvailabilityLogEvent::Edited,
                        'slot_from' => $newFrom,
                        'slot_to' => $newTo,
                        'created_at' => $now,
                    ]);
                });
            });
        }
    }

    /**
     * @param  Collection<int, TrainingPlace>  $places
     */
    public function recordRemoved(Collection $places, Carbon $from, Carbon $to): void
    {
        $now = now();

        foreach ($places as $place) {
            $this->write(fn () => DB::transaction(
                fn () => $this->supersedeMatching($place, $from, $to, $now)
            ));
        }
    }

    private function currentVersion(TrainingPlace $place, Carbon $from, Carbon $to): ?AvailabilityLogEntry
    {
        return AvailabilityLogEntry::query()
            ->where('training_place_id', $place->id)
            ->whereNull('superseded_at')
            ->where('slot_from', $from)
            ->where('slot_to', $to)
            ->first();
    }

    private function supersedeMatching(TrainingPlace $place, Carbon $from, Carbon $to, Carbon $now): void
    {
        $current = $this->currentVersion($place, $from, $to);

        if ($current) {
            $current->update(['superseded_at' => $now]);
        }
    }

    private function write(Closure $operation): void
    {
        try {
            $operation();
        } catch (Throwable $exception) {
            Log::warning('Failed to write availability log entry', ['exception' => $exception]);
        }
    }
}
