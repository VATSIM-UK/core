<?php

namespace App\Libraries;

use Carbon\Carbon;

class Bookings
{
    /**
     * Determine if a booking should be considered past (ended) in UTC.
     */
    public static function isPastUtc(Carbon $dayDate, string $bookingEnd, Carbon $nowUtc): bool
    {
        // Ensure both dates are compared in UTC
        $dayDate = $dayDate->copy()->setTimezone('UTC');
        $endUtc = Carbon::parse($bookingEnd, 'UTC')->setTimezone('UTC');

        // 1️⃣ If the booking date is before today (UTC), it’s past
        if ($dayDate->lt($nowUtc->copy()->startOfDay())) {
            return true;
        }

        // 2️⃣ If the booking is today and has already ended, it’s past
        if ($dayDate->isSameDay($nowUtc)) {
            return $endUtc->lessThanOrEqualTo($nowUtc);
        }

        // 3️⃣ Otherwise it’s upcoming
        return false;
    }
}
