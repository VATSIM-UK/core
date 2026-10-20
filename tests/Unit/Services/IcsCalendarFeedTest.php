<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\IcsService;
use App\Support\Calendar\IcsEvent;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IcsCalendarFeedTest extends TestCase
{
    #[Test]
    public function it_generates_a_feed_containing_multiple_events(): void
    {
        $ics = IcsService::calendar('VATSIM UK bookings', [
            new IcsEvent(
                uid: 'booking-1@vatsim.uk',
                summary: 'EGKK_APP - ATC booking',
                startsAt: Carbon::parse('2026-06-20 12:00:00', 'UTC'),
                endsAt: Carbon::parse('2026-06-20 13:30:00', 'UTC'),
                description: 'Position: EGKK_APP',
                location: 'EGKK_APP',
            ),
            new IcsEvent(
                uid: 'booking-2@vatsim.uk',
                summary: 'EGKK_TWR - Practical exam',
                startsAt: Carbon::parse('2026-06-21 09:00:00', 'UTC'),
                endsAt: Carbon::parse('2026-06-21 10:00:00', 'UTC'),
            ),
        ]);

        $this->assertStringContainsString('BEGIN:VCALENDAR', $ics);
        $this->assertStringContainsString('END:VCALENDAR', $ics);
        $this->assertStringContainsString('X-WR-CALNAME:VATSIM UK bookings', $ics);
        $this->assertStringContainsString('X-WR-TIMEZONE:UTC', $ics);
        $this->assertStringContainsString('REFRESH-INTERVAL;VALUE=DURATION:PT1H', $ics);
        $this->assertSame(2, substr_count($ics, 'BEGIN:VEVENT'));
        $this->assertSame(2, substr_count($ics, 'END:VEVENT'));
        $this->assertStringContainsString('UID:booking-1@vatsim.uk', $ics);
        $this->assertStringContainsString('UID:booking-2@vatsim.uk', $ics);
        $this->assertStringContainsString('DTSTART:20260620T120000Z', $ics);
        $this->assertStringContainsString('DTEND:20260621T100000Z', $ics);
        $this->assertStringContainsString('LOCATION:EGKK_APP', $ics);
        $this->assertStringContainsString('ORGANIZER;CN=VATSIM UK:mailto:no-reply@vatsim.uk', $ics);
    }

    #[Test]
    public function it_generates_a_valid_calendar_without_events(): void
    {
        $ics = IcsService::calendar('VATSIM UK bookings', []);

        $this->assertStringContainsString('BEGIN:VCALENDAR', $ics);
        $this->assertStringContainsString('END:VCALENDAR', $ics);
        $this->assertStringNotContainsString('BEGIN:VEVENT', $ics);
    }

    #[Test]
    public function it_escapes_the_calendar_name(): void
    {
        $ics = IcsService::calendar('Bookings, exams; mentoring', []);

        $this->assertStringContainsString('X-WR-CALNAME:Bookings\\, exams\\; mentoring', $ics);
    }
}
