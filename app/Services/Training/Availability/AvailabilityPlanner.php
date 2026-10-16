<?php

namespace App\Services\Training\Availability;

use App\Models\Cts\Availability;
use App\Services\Training\AvailabilityService;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AvailabilityPlanner
{
    public const STEP_MINUTES = 15;

    private const CROSS_MIDNIGHT = 'cross-midnight';

    private const NON_ALIGNED = 'non-aligned';

    private const TOO_SHORT = 'too-short';

    private const STARTED_TOO_LONG_AGO = 'started-too-long-ago';

    private const ELIGIBLE = 'eligible';

    /**
     * @param  array<int, string>  $dates  Y-m-d strings.
     * @return array{cells: array<int, string>, lockedCells: array<int, string>}
     */
    public function seed(array $dates, Collection $rows, Carbon $now): array
    {
        $boundary = $this->firstCellBoundaryAtOrAfter($now);

        $cells = [];
        $lockedCells = [];

        foreach ($rows as $row) {
            [$start, $end] = $row->bounds();

            if ($this->classifyRow($row, $start, $end, $boundary) === self::ELIGIBLE) {
                $editableFrom = $start->greaterThan($boundary) ? $start : $boundary;

                $cells = array_merge($cells, $this->cellsForRange($editableFrom, $end));

                if ($editableFrom->greaterThan($start)) {
                    $lockedCells = array_merge($lockedCells, $this->cellsForRange($start, $editableFrom));
                }

                continue;
            }

            $lockedCells = array_merge($lockedCells, $this->intersectingCells($start, $end));
        }

        return [
            'cells' => $this->restrictToDates($cells, $dates),
            'lockedCells' => $this->restrictToDates($lockedCells, $dates),
        ];
    }

    /**
     * @param  array<int, string>  $dates  Y-m-d strings.
     * @param  array<int, mixed>  $paintedCells  Y-m-d H:i strings from the client.
     * @param  array<int, int>|null  $seededRowIds  Rows outside it are never removed; null means all rows.
     */
    public function plan(array $dates, Collection $rows, array $paintedCells, Carbon $now, ?array $seededRowIds = null): AvailabilityPlan
    {
        $boundary = $this->firstCellBoundaryAtOrAfter($now);

        $paintedCells = $this->usableCells($paintedCells, $rows, $boundary, $seededRowIds);

        $entries = [];
        $skippedShortRuns = 0;

        foreach ($dates as $date) {
            [$dateEntries, $skipped] = $this->planDate($date, $rows, $paintedCells, $boundary, $seededRowIds);
            $skippedShortRuns += $skipped;
            $entries = array_merge($entries, $dateEntries);
        }

        usort($entries, fn (array $a, array $b) => [
            $a['date'],
            $a['sort']->getTimestamp(),
        ] <=> [
            $b['date'],
            $b['sort']->getTimestamp(),
        ]);

        return new AvailabilityPlan(
            array_map(fn (array $entry) => $entry['change'], $entries),
            $skippedShortRuns,
        );
    }

    /**
     * @param  array<int, string>  $paintedCells
     * @param  array<int, int>|null  $seededRowIds
     * @return array{0: array<int, array{date: string, sort: Carbon, change: AvailabilityChange}>, 1: int}
     */
    private function planDate(string $date, Collection $rows, array $paintedCells, Carbon $boundary, ?array $seededRowIds): array
    {
        $eligible = [];

        foreach ($rows as $row) {
            if ($row->date->format('Y-m-d') !== $date) {
                continue;
            }

            [$start, $end] = $row->bounds();

            if ($this->classifyRow($row, $start, $end, $boundary) !== self::ELIGIBLE) {
                continue;
            }

            $eligible[] = [
                'row' => $row,
                'start' => $start,
                'end' => $end,
                'refStart' => $start->greaterThan($boundary) ? $start : $boundary,
                'refEnd' => $end,
            ];
        }

        usort($eligible, fn (array $a, array $b) => $a['refStart']->getTimestamp() <=> $b['refStart']->getTimestamp());

        $keptRanges = [];
        $skipped = 0;

        foreach ($this->rangesFromCells($this->cellsOnDate($paintedCells, $date)) as [$from, $to]) {
            if ($to->lessThan($from->copy()->addMinutes(AvailabilityService::MINIMUM_SLOT_DURATION_MINUTES))) {
                $skipped++;

                continue;
            }

            $keptRanges[] = [$from, $to];
        }

        $consumed = [];
        $entries = [];

        foreach ($keptRanges as [$from, $to]) {
            $overlapping = [];

            foreach ($eligible as $candidate) {
                if (isset($consumed[spl_object_id($candidate['row'])])) {
                    continue;
                }

                if ($candidate['refStart']->lessThan($to) && $candidate['refEnd']->greaterThan($from)) {
                    $overlapping[] = $candidate;
                }
            }

            if ($overlapping === []) {
                $entries[] = $this->entry($date, $from, new AvailabilityChange(
                    AvailabilityChangeType::Added,
                    null,
                    null,
                    null,
                    $from,
                    $to,
                ));

                continue;
            }

            if (count($overlapping) === 1) {
                $match = $overlapping[0];
                $consumed[spl_object_id($match['row'])] = true;

                $newFrom = $this->keepsPastStart($match, $from) ? $match['start'] : $from;

                if ($newFrom->equalTo($match['start']) && $to->equalTo($match['end'])) {
                    $entries[] = $this->entry($date, $match['start'], new AvailabilityChange(
                        AvailabilityChangeType::Unchanged,
                        $match['row'],
                        $match['start'],
                        $match['end'],
                        $match['start'],
                        $match['end'],
                    ));

                    continue;
                }

                $entries[] = $this->entry($date, $newFrom, new AvailabilityChange(
                    AvailabilityChangeType::Edited,
                    $match['row'],
                    $match['start'],
                    $match['end'],
                    $newFrom,
                    $to,
                ));

                continue;
            }

            $target = $overlapping[0];
            $consumed[spl_object_id($target['row'])] = true;

            $newFrom = $this->keepsPastStart($target, $from) ? $target['start'] : $from;
            $swallowed = [];

            foreach (array_slice($overlapping, 1) as $match) {
                $consumed[spl_object_id($match['row'])] = true;
                $swallowed[] = $match['row'];
            }

            $entries[] = $this->entry($date, $newFrom, new AvailabilityChange(
                AvailabilityChangeType::Merged,
                $target['row'],
                $target['start'],
                $target['end'],
                $newFrom,
                $to,
                $swallowed,
            ));
        }

        foreach ($eligible as $candidate) {
            if (isset($consumed[spl_object_id($candidate['row'])])) {
                continue;
            }

            if ($seededRowIds !== null && ! in_array($candidate['row']->getKey(), $seededRowIds, true)) {
                continue;
            }

            $entries[] = $this->entry($date, $candidate['start'], new AvailabilityChange(
                AvailabilityChangeType::Removed,
                $candidate['row'],
                $candidate['start'],
                $candidate['end'],
                null,
                null,
            ));
        }

        return [$entries, $skipped];
    }

    /**
     * @return array{date: string, sort: Carbon, change: AvailabilityChange}
     */
    private function entry(string $date, Carbon $sort, AvailabilityChange $change): array
    {
        return ['date' => $date, 'sort' => $sort, 'change' => $change];
    }

    /**
     * A started row keeps its past start only while the paint begins at the first editable cell.
     *
     * @param  array{start: Carbon, refStart: Carbon}  $candidate
     */
    private function keepsPastStart(array $candidate, Carbon $from): bool
    {
        return $candidate['start']->lessThan($candidate['refStart']) && $from->equalTo($candidate['refStart']);
    }

    /**
     * Drops malformed, unaligned and past cells, and cells over seeded rows that are no longer
     * editable. Paint over unseen rows is kept so the overlap guard refuses it.
     *
     * @param  array<int, mixed>  $cells
     * @param  array<int, int>|null  $seededRowIds
     * @return array<int, string>
     */
    private function usableCells(array $cells, Collection $rows, Carbon $boundary, ?array $seededRowIds): array
    {
        $locked = [];

        foreach ($rows as $row) {
            if ($seededRowIds !== null && ! in_array($row->getKey(), $seededRowIds, true)) {
                continue;
            }

            [$start, $end] = $row->bounds();

            if ($this->classifyRow($row, $start, $end, $boundary) !== self::ELIGIBLE) {
                foreach ($this->intersectingCells($start, $end) as $cell) {
                    $locked[$cell] = true;
                }
            }
        }

        return array_values(array_filter($cells, function (mixed $cell) use ($locked, $boundary): bool {
            if (! is_string($cell) || isset($locked[$cell]) || ! preg_match('/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}$/', $cell)) {
                return false;
            }

            $instant = Carbon::createFromFormat('!Y-m-d H:i', $cell, 'UTC');

            return $instant->format('Y-m-d H:i') === $cell
                && $this->isAligned($instant)
                && $instant->greaterThanOrEqualTo($boundary);
        }));
    }

    private function classifyRow(Availability $row, Carbon $start, Carbon $end, Carbon $boundary): string
    {
        $from = $row->from->format('H:i:s');
        $to = $row->to->format('H:i:s');

        if ($to < $from && $to !== '00:00:00') {
            return self::CROSS_MIDNIGHT;
        }

        if (! $this->isAligned($start) || ! $this->isAligned($end)) {
            return self::NON_ALIGNED;
        }

        if ($end->lessThan($start->copy()->addMinutes(AvailabilityService::MINIMUM_SLOT_DURATION_MINUTES))) {
            return self::TOO_SHORT;
        }

        if ($end->lessThan($boundary->copy()->addMinutes(AvailabilityService::MINIMUM_SLOT_DURATION_MINUTES))) {
            return self::STARTED_TOO_LONG_AGO;
        }

        return self::ELIGIBLE;
    }

    private function firstCellBoundaryAtOrAfter(Carbon $now): Carbon
    {
        $boundary = Carbon::parse($now->format('Y-m-d H:00'), 'UTC');

        while ($boundary->lessThan($now)) {
            $boundary->addMinutes(self::STEP_MINUTES);
        }

        return $boundary;
    }

    private function isAligned(Carbon $instant): bool
    {
        return $instant->second === 0 && $instant->minute % self::STEP_MINUTES === 0;
    }

    /**
     * @return array<int, string>
     */
    private function cellsForRange(Carbon $start, Carbon $end): array
    {
        $cells = [];

        for ($cursor = $start->copy(); $cursor->lessThan($end); $cursor->addMinutes(self::STEP_MINUTES)) {
            $cells[] = $cursor->format('Y-m-d H:i');
        }

        return $cells;
    }

    /**
     * @return array<int, string>
     */
    private function intersectingCells(Carbon $start, Carbon $end): array
    {
        $cursor = $start->copy()->second(0);
        $cursor->subMinutes($cursor->minute % self::STEP_MINUTES);

        return $this->cellsForRange($cursor, $end);
    }

    /**
     * @param  array<int, string>  $cells
     * @return array<int, array{0: Carbon, 1: Carbon}>
     */
    private function rangesFromCells(array $cells): array
    {
        $keys = array_values(array_unique($cells));
        sort($keys);

        $ranges = [];
        $current = null;

        foreach ($keys as $key) {
            $start = Carbon::parse($key, 'UTC');
            $end = $start->copy()->addMinutes(self::STEP_MINUTES);

            if ($current !== null && $current[1]->equalTo($start)) {
                $current[1] = $end;

                continue;
            }

            if ($current !== null) {
                $ranges[] = $current;
            }

            $current = [$start, $end];
        }

        if ($current !== null) {
            $ranges[] = $current;
        }

        return $ranges;
    }

    /**
     * @param  array<int, string>  $cells
     * @return array<int, string>
     */
    private function cellsOnDate(array $cells, string $date): array
    {
        return array_values(array_filter($cells, fn (string $cell) => substr($cell, 0, 10) === $date));
    }

    /**
     * @param  array<int, string>  $cells
     * @param  array<int, string>  $dates
     * @return array<int, string>
     */
    private function restrictToDates(array $cells, array $dates): array
    {
        $wanted = array_flip($dates);

        $cells = array_values(array_unique(array_filter(
            $cells,
            fn (string $cell) => isset($wanted[substr($cell, 0, 10)]),
        )));

        sort($cells);

        return $cells;
    }
}
