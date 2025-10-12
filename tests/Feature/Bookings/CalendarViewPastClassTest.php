<?php

namespace Tests\Feature\Bookings;

use Carbon\Carbon;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

/**
 * Tiny stub to mimic the booking object your Blade expects.
 * - Provides common fields used in calendar + tooltip partials.
 * - Implements __get() to safely return null for any missing field.
 */
class BookingStub
{
    public string $type;

    public string $from;

    public string $to;

    public string $position;

    /** Minimal relateds used by partials */
    public ?object $member = null;

    public ?object $session = null;

    public ?object $exams = null;

    /** Common tooltip fields (add more if your partial references them) */
    public ?string $time_booked = null;   // <-- your partial requires this

    public ?string $notes = null;

    public ?string $remarks = null;

    public function __construct(
        string $type,
        string $from,
        string $to,
        string $position,
        ?object $member = null,
        ?string $time_booked = null
    ) {
        $this->type = $type;
        $this->from = $from;
        $this->to = $to;
        $this->position = $position;

        // Provide defaults the tooltip can read
        $this->member = $member ?: (object) [
            'cid' => '123456',
            'name' => 'Test User',
        ];

        // time the booking was made (string expected by view)
        $this->time_booked = $time_booked ?: '2025-10-10 09:00:00';
    }

    public function isEvent(): bool
    {
        return $this->type === 'EV';
    }

    public function isExam(): bool
    {
        return $this->type === 'EX';
    }

    public function isMentoring(): bool
    {
        return $this->type === 'ME';
    }

    public function isSeminar(): bool
    {
        return $this->type === 'SE';
    }

    /** Return null for any field the view asks for that we didn't stub explicitly */
    public function __get($name)
    {
        return null;
    }
}

class CalendarViewPastClassTest extends TestCase
{
    protected function tearDown(): void
    {
        Carbon::setTestNow(); // clear frozen time
        parent::tearDown();
    }

    #[Test]
    public function past_bookings_have_is_past_class_and_future_dont()
    {
        // Freeze "now" in UTC so server-side isPast logic is deterministic
        Carbon::setTestNow(Carbon::parse('2025-10-12 12:00:00', 'UTC'));

        $date = Carbon::parse('2025-10-12'); // the month being rendered
        $prevMonth = $date->copy()->subMonth();
        $nextMonth = $date->copy()->addMonth();

        $today = Carbon::parse('2025-10-12');
        $yesterday = $today->copy()->subDay();
        $tomorrow = $today->copy()->addDay();

        // Build the minimal calendar structure the Blade expects
        $calendar = [[
            [
                'date' => $yesterday->toDateString(),
                'bookings' => [
                    new BookingStub('normal', '2025-10-11 10:00:00', '2025-10-11 11:00:00', 'EGLL_TWR'),
                ],
            ],
            [
                'date' => $today->toDateString(),
                'bookings' => [
                    // Past today
                    new BookingStub('normal', '2025-10-12 10:00:00', '2025-10-12 11:00:00', 'EGKK_GND'),
                    // Future today
                    new BookingStub('normal', '2025-10-12 12:01:00', '2025-10-12 13:00:00', 'EGKK_TWR'),
                ],
            ],
            [
                'date' => $tomorrow->toDateString(),
                'bookings' => [
                    new BookingStub('normal', '2025-10-13 09:00:00', '2025-10-13 10:00:00', 'EGLL_APP'),
                ],
            ],
        ]];

        // Render the Blade with all required variables
        $html = view('site.bookings.index', [
            'calendar' => $calendar,
            'date' => $date,
            'prevMonth' => $prevMonth,
            'nextMonth' => $nextMonth,
        ])->render();

        // Smoke checks
        $this->assertStringContainsString('booking-entry', $html);

        // A booking from yesterday should be marked as past
        $this->assertStringContainsString('is-past', $html, 'Expected at least one .is-past element for past bookings.');

        // Future-today (EGKK_TWR) should be present but not marked past
        $this->assertStringContainsString('EGKK_TWR', $html);
        $this->assertStringNotContainsString('EGKK_TWR" class="is-past', $html);

        // Tomorrow (EGLL_APP) should not be marked past
        $this->assertStringContainsString('EGLL_APP', $html);
        $this->assertStringNotContainsString('EGLL_APP" class="is-past', $html);
    }
}
