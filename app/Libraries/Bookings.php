<?php

namespace App\Libraries;

use Carbon\Carbon;

class Bookings
{
    /**
     * Determine if a booking should be considered past (ended) in UTC.
     */
    public static function isPastUtc(Carbon $dayDate, $bookingEnd, Carbon $nowUtc): bool
    {
        $endUtc = Carbon::parse($bookingEnd)->setTimezone('UTC');

        if ($dayDate->lt($nowUtc->copy()->startOfDay())) {
            return true;
        }

        if ($dayDate->isSameDay($nowUtc)) {
            return $endUtc->lte($nowUtc);
        }

        return false;
    }
}