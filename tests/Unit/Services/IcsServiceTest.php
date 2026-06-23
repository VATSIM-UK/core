<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Services\IcsService;
use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class IcsServiceTest extends TestCase
{
    #[Test]
    public function it_generates_valid_ics_content(): void
    {
        $start = Carbon::parse('2026-06-20 12:00:00', 'UTC');
        $end = Carbon::parse('2026-06-20 13:30:00', 'UTC');

        $ics = IcsService::generate(
            uid: 'exam-42@vatsim.uk',
            summary: 'Practical Exam - APP',
            description: 'Exam Type: APP\nPosition: EGKK_APP\nPrimary Examiner: John Smith',
            start: $start,
            end: $end,
            location: 'EGKK_APP',
        );

        $this->assertStringContainsString('BEGIN:VCALENDAR', $ics);
        $this->assertStringContainsString('END:VCALENDAR', $ics);
        $this->assertStringContainsString('BEGIN:VEVENT', $ics);
        $this->assertStringContainsString('END:VEVENT', $ics);
        $this->assertStringContainsString('VERSION:2.0', $ics);
        $this->assertStringContainsString('PRODID:-//VATSIM UK//Core//EN', $ics);
        $this->assertStringContainsString('METHOD:PUBLISH', $ics);
        $this->assertStringContainsString('UID:exam-42@vatsim.uk', $ics);
        $this->assertStringContainsString('DTSTART:20260620T120000Z', $ics);
        $this->assertStringContainsString('DTEND:20260620T133000Z', $ics);
        $this->assertStringContainsString('SUMMARY:Practical Exam - APP', $ics);
        $this->assertStringContainsString('LOCATION:EGKK_APP', $ics);
        $this->assertStringContainsString('ORGANIZER;CN=VATSIM UK Training Department:mailto:atc-training@vatsim.uk', $ics);
    }

    #[Test]
    public function it_escapes_special_characters(): void
    {
        $start = Carbon::parse('2026-06-20 12:00:00', 'UTC');
        $end = Carbon::parse('2026-06-20 13:00:00', 'UTC');

        $ics = IcsService::generate(
            uid: 'test-1@vatsim.uk',
            summary: 'Test;Summary,With:Special\\Chars',
            description: "Line one\nLine two\nComma, semicolon; backslash\\",
            start: $start,
            end: $end,
        );

        $this->assertStringContainsString('SUMMARY:Test\\;Summary\\,With:Special\\\\Chars', $ics);
        $this->assertStringContainsString('DESCRIPTION:Line one\\nLine two\\nComma\\, semicolon\\; backslash\\\\', $ics);
    }

    #[Test]
    public function it_handles_empty_location(): void
    {
        $start = Carbon::parse('2026-06-20 12:00:00', 'UTC');
        $end = Carbon::parse('2026-06-20 13:00:00', 'UTC');

        $ics = IcsService::generate(
            uid: 'session-1@vatsim.uk',
            summary: 'Mentoring Session',
            description: 'A mentoring session',
            start: $start,
            end: $end,
        );

        $this->assertStringNotContainsString('LOCATION:', $ics);
        $this->assertStringContainsString('ORGANIZER;CN=VATSIM UK Training Department:mailto:atc-training@vatsim.uk', $ics);
    }

    #[Test]
    public function it_uses_deterministic_uid(): void
    {
        $start = Carbon::parse('2026-06-20 12:00:00', 'UTC');
        $end = Carbon::parse('2026-06-20 13:00:00', 'UTC');

        $ics1 = IcsService::generate(
            uid: 'exam-42@vatsim.uk',
            summary: 'Test',
            description: 'Test',
            start: $start,
            end: $end,
        );

        $ics2 = IcsService::generate(
            uid: 'exam-42@vatsim.uk',
            summary: 'Test',
            description: 'Test',
            start: $start,
            end: $end,
        );

        // Both should contain the same UID
        $this->assertStringContainsString('UID:exam-42@vatsim.uk', $ics1);
        $this->assertStringContainsString('UID:exam-42@vatsim.uk', $ics2);
    }

    #[Test]
    public function it_formats_dates_in_utc_correctly(): void
    {
        // Test with a non-UTC input timezone
        $start = Carbon::parse('2026-06-20 14:00:00', 'Europe/London');
        $end = Carbon::parse('2026-06-20 15:00:00', 'Europe/London');

        $ics = IcsService::generate(
            uid: 'test-tz@vatsim.uk',
            summary: 'Timezone Test',
            description: 'Testing timezone conversion',
            start: $start,
            end: $end,
        );

        // Europe/London on 20 June is UTC+1 (BST), so 14:00 BST = 13:00 UTC
        $this->assertStringContainsString('DTSTART:20260620T130000Z', $ics);
        $this->assertStringContainsString('DTEND:20260620T140000Z', $ics);
    }

    #[Test]
    public function it_includes_dtstamp(): void
    {
        $start = Carbon::parse('2026-06-20 12:00:00', 'UTC');
        $end = Carbon::parse('2026-06-20 13:00:00', 'UTC');

        $ics = IcsService::generate(
            uid: 'test-stamp@vatsim.uk',
            summary: 'Test',
            description: 'Test',
            start: $start,
            end: $end,
        );

        // DTSTAMP should be in UTC format
        $this->assertMatchesRegularExpression('/DTSTAMP:\d{8}T\d{6}Z/', $ics);
    }
}
