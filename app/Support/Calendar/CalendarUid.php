<?php

declare(strict_types=1);

namespace App\Support\Calendar;

use App\Models\Booking;
use App\Models\Cts\ExamBooking;
use App\Models\Cts\Session;

final class CalendarUid
{
    /**
     * Shared by the feed and the accept/reschedule emails so an event keeps one
     * identity wherever it is added from.
     *
     * Keyed on bookable_type, not type: legacy CTS imports point at a CTS
     * booking id, which would collide with exam and session ids.
     */
    public static function forBooking(Booking $booking): string
    {
        if ($booking->bookable_id !== null) {
            if ($booking->bookable_type === ExamBooking::class) {
                return self::forExam((int) $booking->bookable_id);
            }

            if ($booking->bookable_type === Session::class) {
                return self::forSession((int) $booking->bookable_id);
            }
        }

        return 'booking-'.$booking->getKey().'@vatsim.uk';
    }

    public static function forExam(int $examBookingId): string
    {
        return "exam-{$examBookingId}@vatsim.uk";
    }

    public static function forSession(int $sessionId): string
    {
        return "session-{$sessionId}@vatsim.uk";
    }
}
