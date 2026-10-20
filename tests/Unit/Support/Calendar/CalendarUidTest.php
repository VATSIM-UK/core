<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Calendar;

use App\Models\Booking;
use App\Models\Cts\Booking as CtsBooking;
use App\Models\Cts\ExamBooking;
use App\Models\Cts\Session;
use App\Support\Calendar\CalendarUid;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CalendarUidTest extends TestCase
{
    #[Test]
    public function it_uses_the_booking_id_for_standard_bookings(): void
    {
        $booking = Booking::factory()->make([
            'id' => 99,
            'type' => Booking::TYPE_STANDARD,
        ]);

        $this->assertSame('booking-99@vatsim.uk', CalendarUid::forBooking($booking));
    }

    #[Test]
    public function it_matches_the_email_uid_for_exams(): void
    {
        $booking = Booking::factory()->make([
            'id' => 99,
            'type' => Booking::TYPE_EXAM,
            'bookable_type' => ExamBooking::class,
            'bookable_id' => 42,
        ]);

        $this->assertSame('exam-42@vatsim.uk', CalendarUid::forBooking($booking));
        $this->assertSame(CalendarUid::forExam(42), CalendarUid::forBooking($booking));
    }

    #[Test]
    public function it_matches_the_email_uid_for_mentoring_sessions(): void
    {
        $booking = Booking::factory()->make([
            'id' => 99,
            'type' => Booking::TYPE_MENTORING,
            'bookable_type' => Session::class,
            'bookable_id' => 7,
        ]);

        $this->assertSame('session-7@vatsim.uk', CalendarUid::forBooking($booking));
        $this->assertSame(CalendarUid::forSession(7), CalendarUid::forBooking($booking));
    }

    #[Test]
    public function it_falls_back_to_the_booking_id_when_the_bookable_id_is_missing(): void
    {
        $booking = Booking::factory()->make([
            'id' => 99,
            'type' => Booking::TYPE_EXAM,
            'bookable_type' => ExamBooking::class,
            'bookable_id' => null,
        ]);

        $this->assertSame('booking-99@vatsim.uk', CalendarUid::forBooking($booking));
    }

    #[Test]
    public function it_falls_back_to_the_booking_id_for_legacy_cts_imports(): void
    {
        // CTS booking ids share a numeric space with exam and session ids, so
        // they must not borrow the exam/session prefixes.
        $booking = Booking::factory()->make([
            'id' => 99,
            'type' => Booking::TYPE_EXAM,
            'bookable_type' => CtsBooking::class,
            'bookable_id' => 42,
        ]);

        $this->assertSame('booking-99@vatsim.uk', CalendarUid::forBooking($booking));
    }
}
