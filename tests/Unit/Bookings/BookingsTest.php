<?php

namespace Tests\Unit\Bookings;

use App\Libraries\Bookings;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class BookingsTest extends TestCase
{
    #[Test]
    public function previous_day_is_always_past()
    {
        $nowUtc = Carbon::parse('2025-10-12 12:00:00', 'UTC');
        $day = Carbon::parse('2025-10-11', 'UTC'); // yesterday
        $end = '2025-10-11 23:59:00';              // any end time yesterday

        $this->assertTrue(Bookings::isPastUtc($day, $end, $nowUtc));
    }

    #[Test]
    public function today_before_now_is_past()
    {
        $nowUtc = Carbon::parse('2025-10-12 12:00:00', 'UTC');
        $day = Carbon::parse('2025-10-12', 'UTC');
        $end = '2025-10-12 11:59:00';

        $this->assertTrue(Bookings::isPastUtc($day, $end, $nowUtc));
    }

    #[Test]
    public function today_equal_now_is_past()
    {
        $nowUtc = Carbon::parse('2025-10-12 12:00:00', 'UTC');
        $day = Carbon::parse('2025-10-12', 'UTC');
        $end = '2025-10-12 12:00:00';

        $this->assertTrue(Bookings::isPastUtc($day, $end, $nowUtc));
    }

    #[Test]
    public function today_after_now_is_not_past()
    {
        $nowUtc = Carbon::parse('2025-10-12 12:00:00', 'UTC');
        $day = Carbon::parse('2025-10-12', 'UTC');
        $end = '2025-10-12 12:01:00';

        $this->assertFalse(Bookings::isPastUtc($day, $end, $nowUtc));
    }

    #[Test]
    public function future_day_is_not_past()
    {
        $nowUtc = Carbon::parse('2025-10-12 12:00:00', 'UTC');
        $day = Carbon::parse('2025-10-13', 'UTC');
        $end = '2025-10-13 08:00:00';

        $this->assertFalse(Bookings::isPastUtc($day, $end, $nowUtc));
    }

    #[Test]
    public function timezone_on_booking_end_is_respected()
    {
        // Now is 12:00 UTC
        $nowUtc = Carbon::parse('2025-10-12 12:00:00', 'UTC');
        $day = Carbon::parse('2025-10-12', 'UTC');

        // End at 13:00 local (+02:00) => 11:00 UTC  -> should be past
        $endPastTz = '2025-10-12T13:00:00+02:00';
        $this->assertTrue(Bookings::isPastUtc($day, $endPastTz, $nowUtc));

        // End at 13:30 local (+02:00) => 11:30 UTC  -> still past
        $endStillPastTz = '2025-10-12T13:30:00+02:00';
        $this->assertTrue(Bookings::isPastUtc($day, $endStillPastTz, $nowUtc));

        // End at 16:00 local (+02:00) => 14:00 UTC  -> not past
        $endFutureTz = '2025-10-12T16:00:00+02:00';
        $this->assertFalse(Bookings::isPastUtc($day, $endFutureTz, $nowUtc));
    }

    #[Test]
    public function midnight_boundary_cases()
    {
        // At midnight UTC at the start of 2025-10-12
        $nowUtc = Carbon::parse('2025-10-12 00:00:00', 'UTC');

        // Previous day: always past
        $yesterday = Carbon::parse('2025-10-11', 'UTC');
        $endYesterday = '2025-10-11 23:59:59';
        $this->assertTrue(Bookings::isPastUtc($yesterday, $endYesterday, $nowUtc));

        // Today, end exactly at midnight -> should be past (<= now)
        $today = Carbon::parse('2025-10-12', 'UTC');
        $endAtMidnight = '2025-10-12 00:00:00';
        $this->assertTrue(Bookings::isPastUtc($today, $endAtMidnight, $nowUtc));

        // Today, 00:00:01 -> not past
        $endJustAfter = '2025-10-12 00:00:01';
        $this->assertFalse(Bookings::isPastUtc($today, $endJustAfter, $nowUtc));
    }
}
