<?php

declare(strict_types=1);

namespace App\Support\Bookings;

use Carbon\Carbon;
use Carbon\CarbonPeriod;

final class BookingDays
{
    /**
     * Splits a calendar row into one copy per day it covers within the range.
     * Each copy's from/to are clipped to its day ("00:00"/"24:00" at a cut),
     * while time_from/time_to, starts_on and ends_on keep the real times.
     *
     * @param  object{date: string, from: string, to: string, ends_on: string}  $row
     * @return list<object>
     */
    public static function split(object $row, string $firstDay, string $lastDay): array
    {
        $lastCovered = $row->to === '00:00' && $row->ends_on > $row->date
            ? Carbon::parse($row->ends_on)->subDay()->toDateString()
            : $row->ends_on;

        $parts = [];

        foreach (CarbonPeriod::create($row->date, $lastCovered) as $day) {
            $date = $day->toDateString();

            if ($date < $firstDay || $date > $lastDay) {
                continue;
            }

            $part = clone $row;
            $part->date = $date;
            $part->from = $date === $row->date ? $row->from : '00:00';
            $part->to = $date === $row->ends_on ? $row->to : '24:00';
            $part->time_from = $row->from;
            $part->time_to = $row->to;
            $part->starts_on = $row->date;
            $part->continues_before = $date !== $row->date;
            $part->continues_after = $date !== $lastCovered;

            $parts[] = $part;
        }

        return $parts;
    }
}
