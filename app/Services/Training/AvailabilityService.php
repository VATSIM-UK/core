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

    /**
     * The order in which change types are applied. Rewrites and removals run
     * before additions so space freed or reshaped by the plan is available.
     */
    private const CHANGE_APPLICATION_ORDER = [
        AvailabilityChangeType::Removed->value => 0,
        AvailabilityChangeType::Merged->value => 1,
        AvailabilityChangeType::Edited->value => 2,
        AvailabilityChangeType::Unchanged->value => 3,
        AvailabilityChangeType::Added->value => 4,
    ];

    public function resolveMemberId(int $cid): ?int
    {
        return Member::where('cid', $cid)->value('id');
    }

    /**
     * Saved student availability rows for a member, ordered by date then start time.
     *
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
     * Apply a planner change list to the CTS rows, writing a matching mentor log entry per change.
     *
     * @param  array<int, AvailabilityChange>  $changes
     * @return array{added: int, edited: int, merged: int, removed: int, refused: bool}
     */
    public function applyChanges(int $studentId, array $changes): array
    {
        if ($this->wouldOverlap($studentId, $changes)) {
            Log::warning('Availability save refused, overlapping slots', ['student_id' => $studentId]);

            return [
                'added' => 0,
                'edited' => 0,
                'merged' => 0,
                'removed' => 0,
                'refused' => true,
            ];
        }

        $counts = [
            'added' => 0,
            'edited' => 0,
            'merged' => 0,
            'removed' => 0,
        ];

        $ordered = $this->sortChanges($changes);

        DB::connection('cts')->transaction(function () use ($ordered, $studentId, &$counts): void {
            $service = app(AvailabilityLogService::class);
            $places = $this->activeTrainingPlaces($service, $studentId);

            foreach ($ordered as $change) {
                switch ($change->type) {
                    case AvailabilityChangeType::Removed:
                        $change->existing?->delete();

                        if ($change->oldFrom && $change->oldTo) {
                            $service->recordRemoved($places, $change->oldFrom, $change->oldTo);
                        }

                        $counts['removed']++;

                        break;

                    case AvailabilityChangeType::Merged:
                        $change->existing?->update([
                            'date' => $change->newFrom->toDateString(),
                            'from' => $change->newFrom->format('H:i:s'),
                            'to' => $change->newTo->format('H:i:s'),
                        ]);

                        $service->recordMerged($places, $change->oldFrom, $change->oldTo, $change->newFrom, $change->newTo);
                        $counts['merged']++;

                        foreach ($change->swallowed as $row) {
                            [$from, $to] = $this->boundsFor($row);
                            $row->delete();
                            $service->recordRemoved($places, $from, $to);
                            $counts['removed']++;
                        }

                        break;

                    case AvailabilityChangeType::Edited:
                        $change->existing?->update([
                            'date' => $change->newFrom->toDateString(),
                            'from' => $change->newFrom->format('H:i:s'),
                            'to' => $change->newTo->format('H:i:s'),
                        ]);

                        $service->recordEdited($places, $change->oldFrom, $change->oldTo, $change->newFrom, $change->newTo);
                        $counts['edited']++;

                        break;

                    case AvailabilityChangeType::Added:
                        Availability::create([
                            'student_id' => $studentId,
                            'type' => 'S',
                            'date' => $change->newFrom->toDateString(),
                            'from' => $change->newFrom->format('H:i:s'),
                            'to' => $change->newTo->format('H:i:s'),
                        ]);

                        $service->recordAdded($places, $change->newFrom, $change->newTo);
                        $counts['added']++;

                        break;

                    case AvailabilityChangeType::Unchanged:
                        break;
                }
            }
        });

        return [
            'added' => $counts['added'],
            'edited' => $counts['edited'],
            'merged' => $counts['merged'],
            'removed' => $counts['removed'],
            'refused' => false,
        ];
    }

    /**
     * Detect whether a planned range would collide with another planned range or
     * with a stored row the plan does not touch.
     *
     * Only comparisons that involve at least one change are made: a planned range
     * (the new bounds of an addition, edit or merge) against the other planned
     * ranges, and against the stored bounds of every remaining row that no change
     * rewrites or removes. Two untouched rows that overlap are pre-existing data
     * and are never a reason to refuse.
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

        $existing = Availability::query()
            ->where('student_id', $studentId)
            ->where('type', 'S')
            ->get();

        foreach ($existing as $row) {
            if (isset($touchedKeys[$row->getKey()])) {
                continue;
            }

            $bounds = $this->boundsFor($row);

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
     * Order changes deterministically: by date, then rewrites/removals before additions,
     * then by start instant.
     *
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
     * The UTC instants a stored row spans, extending into the next day when its
     * end time is earlier than its start.
     *
     * @return array{0: Carbon, 1: Carbon}
     */
    private function boundsFor(Availability $row): array
    {
        $date = $row->date->format('Y-m-d');

        $start = Carbon::parse($date.' '.$row->from->format('H:i:s'), 'UTC');
        $end = Carbon::parse($date.' '.$row->to->format('H:i:s'), 'UTC');

        if ($row->to->format('H:i:s') < $row->from->format('H:i:s')) {
            $end->addDay();
        }

        return [$start, $end];
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

        $overlapExists = Availability::query()
            ->where('student_id', $studentId)
            ->where('type', 'S')
            ->when($ignoreId, fn (Builder $query) => $query->where('id', '!=', $ignoreId))
            ->whereBetween('date', [$startUtc->clone()->subDay()->toDateString(), $startUtc->clone()->addDay()->toDateString()])
            ->get()
            ->contains(function (Availability $slot) use ($startUtc, $endUtc) {
                $slotStart = Carbon::parse($slot->date->format('Y-m-d').' '.$slot->from->format('H:i:s'), 'UTC');
                $slotEnd = Carbon::parse($slot->date->format('Y-m-d').' '.$slot->to->format('H:i:s'), 'UTC');

                if ($slot->to->format('H:i:s') < $slot->from->format('H:i:s')) {
                    $slotEnd->addDay();
                }

                return $startUtc->lessThan($slotEnd) && $endUtc->greaterThan($slotStart);
            });

        if ($overlapExists) {
            return [false, 'This availability slot overlaps with an existing entry.'];
        }

        return [true, 'Valid'];
    }
}
