<?php

declare(strict_types=1);

namespace App\Support\Calendar;

use App\Models\Cts\Session;
use Carbon\Carbon;

final class CalendarSequence
{
    private const EPOCH = '2020-01-01 00:00:00';

    public static function forSession(Session $session): int
    {
        if (blank($session->taken_time)) {
            return 0;
        }

        $seconds = Carbon::parse(self::EPOCH, 'UTC')->diffInSeconds(Carbon::parse($session->taken_time));

        return max(0, (int) $seconds);
    }
}
