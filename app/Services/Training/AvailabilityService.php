<?php

namespace App\Services\Training;

use App\Models\Cts\Availability;
use App\Models\Cts\Member;
use App\Services\Training\Availability\AvailabilityChange;
use App\Services\Training\Availability\AvailabilityChangeType;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AvailabilityService
{
    public const MINIMUM_SLOT_DURATION_MINUTES = 45;

    public const MAX_FUTURE_DAYS = 31;

    /** Removals and rewrites run before additions so freed space is available. */
    private const CHANGE_APPLICATION_ORDER = [
        AvailabilityChangeType::Removed->value => 0,
        AvailabilityChangeType::Merged->value => 1,
        AvailabilityChangeType::Edited->value => 2,
        AvailabilityChangeType::Unchanged->value => 3,
        AvailabilityChangeType::Added->value => 4,
    ];

    /** The last date a student may add availability for; the whole day is allowed. */
    public static function lastAllowedDate(Carbon $now): Carbon
    {
        return $now->copy()->startOfDay()->addDays(self::MAX_FUTURE_DAYS);
    }

    /** The first date a student may add availability for. */
    public static function firstAllowedDate(Carbon $now): Carbon
    {
        return $now->copy()->startOfDay();
    }

    public function isBeyondFutureWindow(Carbon $start, ?Carbon $now = null): bool
    {
        return $start->copy()->startOfDay()->greaterThan(
            static::lastAllowedDate($now ?? now()->utc())
        );
    }

    /** Whether the student holds availability inside the window the check counts. */
    public function hasAvailabilityWithinWindow(int $memberId): bool
    {
        $now = now()->utc();

        return Availability::query()
            ->where('student_id', $memberId)
            ->where('type', 'S')
            ->where('date', '>=', static::firstAllowedDate($now)->toDateString())
            ->where('date', '<=', static::lastAllowedDate($now)->toDateString())
            ->exists();
    }

    public function resolveMemberId(int $cid): ?int
    {
        return Member::where('cid', $cid)->value('id');
    }

    public function getFutureAvailabilityQuery(int $memberId): Builder
    {
        return Availability::query()
            ->where('student_id', $memberId)
            ->where('type', 'S')
            ->where(function (Builder $query) {
                $now = now()->utc();
                $query->whereDate('date', '>', $now->toDateString())
                    ->orWhere(function ($q) use ($now) {
                        $q->whereDate('date', $now->toDateString())
                            ->whereTime('to', '>', $now->format('H:i:s'));
                    });
            });
    }

    /**
     * @return Collection<int, Availability>
     */
    public function availabilityRowsFor(int $memberId, Carbon $from, Carbon $to): Collection
    {
        return Availability::query()
            ->where('student_id', $memberId)
            ->where('type', 'S')
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->orderBy('date')
            ->orderBy('from')
            ->get();
    }

    /**
     * Log entries live on another connection, so they are written only after the CTS commit.
     *
     * @param  array<int, AvailabilityChange>  $changes
     * @return array{added: int, edited: int, merged: int, removed: int, refused: bool}
     */
    private function storedEndTime(Carbon $end): string
    {
        return $end->format('H:i:s') === '00:00:00' ? '24:00:00' : $end->format('H:i:s');
    }

    public function applyChanges(int $studentId, array $changes): array
    {
        $counts = [
            'added' => 0,
            'edited' => 0,
            'merged' => 0,
            'removed' => 0,
            'refused' => false,
        ];

        $ordered = $this->sortChanges($changes);
        $logs = [];

        $applied = DB::connection('cts')->transaction(function () use ($ordered, $studentId, &$counts, &$logs): bool {
            foreach ($ordered as $change) {
                if ($change->newFrom && $this->isBeyondFutureWindow($change->newFrom)) {
                    Log::warning('Availability save refused, beyond the future window', ['student_id' => $studentId]);

                    return false;
                }
            }

            if ($this->wouldOverlap($studentId, $ordered)) {
                return false;
            }

            foreach ($ordered as $change) {
                switch ($change->type) {
                    case AvailabilityChangeType::Removed:
                        $change->existing?->delete();

                        if ($change->oldFrom && $change->oldTo) {
                            $logs[] = fn (AvailabilityLogService $service, Collection $places) => $service->recordRemoved($places, $change->oldFrom, $change->oldTo);
                        }

                        $counts['removed']++;

                        break;

                    case AvailabilityChangeType::Merged:
                        $change->existing?->update([
                            'date' => $change->newFrom->toDateString(),
                            'from' => $change->newFrom->format('H:i:s'),
                            'to' => $this->storedEndTime($change->newTo),
                        ]);

                        $logs[] = fn (AvailabilityLogService $service, Collection $places) => $service->recordMerged($places, $change->oldFrom, $change->oldTo, $change->newFrom, $change->newTo);
                        $counts['merged']++;

                        foreach ($change->swallowed as $row) {
                            [$from, $to] = $row->bounds();
                            $row->delete();
                            $logs[] = fn (AvailabilityLogService $service, Collection $places) => $service->recordRemoved($places, $from, $to);
                            $counts['removed']++;
                        }

                        break;

                    case AvailabilityChangeType::Edited:
                        $change->existing?->update([
                            'date' => $change->newFrom->toDateString(),
                            'from' => $change->newFrom->format('H:i:s'),
                            'to' => $this->storedEndTime($change->newTo),
                        ]);

                        $logs[] = fn (AvailabilityLogService $service, Collection $places) => $service->recordEdited($places, $change->oldFrom, $change->oldTo, $change->newFrom, $change->newTo);
                        $counts['edited']++;

                        break;

                    case AvailabilityChangeType::Added:
                        Availability::create([
                            'student_id' => $studentId,
                            'type' => 'S',
                            'date' => $change->newFrom->toDateString(),
                            'from' => $change->newFrom->format('H:i:s'),
                            'to' => $this->storedEndTime($change->newTo),
                        ]);

                        $logs[] = fn (AvailabilityLogService $service, Collection $places) => $service->recordAdded($places, $change->newFrom, $change->newTo);
                        $counts['added']++;

                        break;

                    case AvailabilityChangeType::Unchanged:
                        break;
                }
            }

            return true;
        });

        if (! $applied) {
            Log::warning('Availability save refused, overlapping slots', ['student_id' => $studentId]);

            return [...$counts, 'refused' => true];
        }

        if ($logs !== []) {
            $service = app(AvailabilityLogService::class);
            $places = $this->activeTrainingPlaces($service, $studentId);

            foreach ($logs as $log) {
                $log($service, $places);
            }
        }

        return $counts;
    }

    /**
     * Add each range, merging it with every stored row it overlaps.
     *
     * @param  array<int, array{0: Carbon, 1: Carbon}>  $ranges
     * @return array{added: int, edited: int, merged: int, removed: int, refused: bool}
     */
    public function addSlots(int $studentId, array $ranges): array
    {
        $changes = [];
        $consumed = [];

        foreach ($ranges as [$start]) {
            if ($this->isBeyondFutureWindow($start)) {
                Log::warning('Availability add refused, beyond the future window', ['student_id' => $studentId]);

                return ['added' => 0, 'edited' => 0, 'merged' => 0, 'removed' => 0, 'refused' => true];
            }
        }

        foreach ($ranges as [$start, $end]) {
            $overlapping = $this->rowsOverlapping($studentId, $start, $end)
                ->reject(fn (Availability $row) => isset($consumed[$row->getKey()]))
                ->values();

            if ($overlapping->isEmpty()) {
                $changes[] = new AvailabilityChange(AvailabilityChangeType::Added, null, null, null, $start, $end);

                continue;
            }

            $from = $start->copy();
            $to = $end->copy();

            foreach ($overlapping as $row) {
                $consumed[$row->getKey()] = true;
                [$rowFrom, $rowTo] = $row->bounds();
                $from = $from->min($rowFrom);
                $to = $to->max($rowTo);
            }

            $target = $overlapping->shift();
            [$oldFrom, $oldTo] = $target->bounds();

            $changes[] = new AvailabilityChange(AvailabilityChangeType::Merged, $target, $oldFrom, $oldTo, $from, $to, $overlapping->all());
        }

        return $this->applyChanges($studentId, $changes);
    }

    /**
     * @return array{added: int, edited: int, merged: int, removed: int, refused: bool}
     */
    public function editSlot(Availability $row, Carbon $start, Carbon $end): array
    {
        [$oldFrom, $oldTo] = $row->bounds();

        return $this->applyChanges($row->student_id, [
            new AvailabilityChange(AvailabilityChangeType::Edited, $row, $oldFrom, $oldTo, $start, $end),
        ]);
    }

    /**
     * @param  iterable<int, Availability>  $rows
     * @return array{added: int, edited: int, merged: int, removed: int, refused: bool}
     */
    public function removeSlots(int $studentId, iterable $rows): array
    {
        $changes = [];

        foreach ($rows as $row) {
            [$from, $to] = $row->bounds();
            $changes[] = new AvailabilityChange(AvailabilityChangeType::Removed, $row, $from, $to, null, null);
        }

        return $this->applyChanges($studentId, $changes);
    }

    /**
     * @return Collection<int, Availability>
     */
    private function rowsOverlapping(int $studentId, Carbon $start, Carbon $end): Collection
    {
        return Availability::query()
            ->where('student_id', $studentId)
            ->where('type', 'S')
            ->whereBetween('date', [$start->copy()->subDay()->toDateString(), $end->toDateString()])
            ->orderBy('date')
            ->orderBy('from')
            ->get()
            ->filter(fn (Availability $row) => $this->rangesOverlap([$start, $end], $row->bounds()))
            ->values();
    }

    /**
     * Checks planned ranges against each other and against untouched rows; overlap
     * between two untouched rows is pre-existing and ignored.
     *
     * @param  array<int, AvailabilityChange>  $changes
     */
    private function wouldOverlap(int $studentId, array $changes): bool
    {
        $touchedKeys = [];
        $planned = [];

        foreach ($changes as $change) {
            if ($change->type === AvailabilityChangeType::Unchanged) {
                continue;
            }

            if ($change->existing) {
                $touchedKeys[$change->existing->getKey()] = true;
            }

            foreach ($change->swallowed as $row) {
                $touchedKeys[$row->getKey()] = true;
            }

            $isRewrite = in_array($change->type, [
                AvailabilityChangeType::Added,
                AvailabilityChangeType::Edited,
                AvailabilityChangeType::Merged,
            ], true);

            if ($isRewrite && $change->newFrom && $change->newTo) {
                $planned[] = [$change->newFrom->copy(), $change->newTo->copy()];
            }
        }

        if ($planned === []) {
            return false;
        }

        $count = count($planned);

        for ($i = 0; $i < $count; $i++) {
            for ($j = $i + 1; $j < $count; $j++) {
                if ($this->rangesOverlap($planned[$i], $planned[$j])) {
                    return true;
                }
            }
        }

        $starts = array_map(fn (array $range) => $range[0]->getTimestamp(), $planned);
        $ends = array_map(fn (array $range) => $range[1]->getTimestamp(), $planned);

        $existing = Availability::query()
            ->where('student_id', $studentId)
            ->where('type', 'S')
            ->whereBetween('date', [
                Carbon::createFromTimestamp(min($starts), 'UTC')->subDay()->toDateString(),
                Carbon::createFromTimestamp(max($ends), 'UTC')->toDateString(),
            ])
            ->lockForUpdate()
            ->get();

        foreach ($existing as $row) {
            if (isset($touchedKeys[$row->getKey()])) {
                continue;
            }

            $bounds = $row->bounds();

            foreach ($planned as $range) {
                if ($this->rangesOverlap($range, $bounds)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * @param  array{0: Carbon, 1: Carbon}  $a
     * @param  array{0: Carbon, 1: Carbon}  $b
     */
    private function rangesOverlap(array $a, array $b): bool
    {
        return $a[0]->lessThan($b[1]) && $b[0]->lessThan($a[1]);
    }

    /**
     * @param  array<int, AvailabilityChange>  $changes
     * @return array<int, AvailabilityChange>
     */
    private function sortChanges(array $changes): array
    {
        $ordered = array_values($changes);

        usort($ordered, function (AvailabilityChange $a, AvailabilityChange $b): int {
            $aAnchor = $a->newFrom ?? $a->oldFrom;
            $bAnchor = $b->newFrom ?? $b->oldFrom;

            return [
                $aAnchor?->format('Y-m-d') ?? '',
                self::CHANGE_APPLICATION_ORDER[$a->type->value],
                $aAnchor?->getTimestamp() ?? 0,
            ] <=> [
                $bAnchor?->format('Y-m-d') ?? '',
                self::CHANGE_APPLICATION_ORDER[$b->type->value],
                $bAnchor?->getTimestamp() ?? 0,
            ];
        });

        return $ordered;
    }

    /**
     * @return Collection<int, \App\Models\Training\TrainingPlace\TrainingPlace>
     */
    private function activeTrainingPlaces(AvailabilityLogService $service, int $studentId): Collection
    {
        $cid = Member::where('id', $studentId)->value('cid');

        if (! $cid) {
            return collect();
        }

        return $service->activeTrainingPlacesForAccount((int) $cid);
    }

    public function formatDuration(Carbon $date, Carbon $from, Carbon $to): string
    {
        $start = Carbon::parse($date->format('Y-m-d').' '.$from->format('H:i:s'));
        $end = Carbon::parse($date->format('Y-m-d').' '.$to->format('H:i:s'));

        if ($to->format('H:i:s') < $from->format('H:i:s')) {
            $end->addDay();
        }

        $minutes = max(0, $start->diffInMinutes($end));
        $hours = intdiv($minutes, 60);
        $rem = $minutes % 60;

        if ($hours === 0) {
            return "{$rem}m";
        }
        if ($rem === 0) {
            return "{$hours}h";
        }

        return "{$hours}h {$rem}m";
    }

    public function meetsMinimumDuration(Carbon $startUtc, Carbon $endUtc): bool
    {
        return $startUtc->diffInMinutes($endUtc) >= static::MINIMUM_SLOT_DURATION_MINUTES;
    }

    public function isSlotValid(int $studentId, Carbon $startUtc, Carbon $endUtc, ?int $ignoreId = null): array
    {
        if ($startUtc->greaterThanOrEqualTo($endUtc)) {
            return [false, 'The availability end time must be after the start time.'];
        }

        if (! $this->meetsMinimumDuration($startUtc, $endUtc)) {
            return [false, 'Availability slots must be at least '.static::MINIMUM_SLOT_DURATION_MINUTES.' minutes long.'];
        }

        if ($startUtc->isPast()) {
            return [false, 'Availability cannot be in the past.'];
        }

        if ($this->isBeyondFutureWindow($startUtc)) {
            return [false, 'Availability cannot be more than '.self::MAX_FUTURE_DAYS.' days in the future.'];
        }

        $overlapExists = Availability::query()
            ->where('student_id', $studentId)
            ->where('type', 'S')
            ->when($ignoreId, fn (Builder $query) => $query->where('id', '!=', $ignoreId))
            ->whereBetween('date', [$startUtc->clone()->subDay()->toDateString(), $startUtc->clone()->addDay()->toDateString()])
            ->get()
            ->contains(function (Availability $slot) use ($startUtc, $endUtc) {
                [$slotStart, $slotEnd] = $slot->bounds();

                return $startUtc->lessThan($slotEnd) && $endUtc->greaterThan($slotStart);
            });

        if ($overlapExists) {
            return [false, 'This availability slot overlaps with an existing entry.'];
        }

        return [true, 'Valid'];
    }
}
