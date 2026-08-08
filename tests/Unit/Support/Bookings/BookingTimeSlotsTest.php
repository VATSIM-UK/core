<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Bookings;

use App\Support\Bookings\BookingTimeSlots;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BookingTimeSlotsTest extends TestCase
{
    #[Test]
    public function it_lists_all_quarter_hour_slots_for_a_day(): void
    {
        $slots = BookingTimeSlots::all();

        $this->assertCount(96, $slots);
        $this->assertSame('00:00', $slots[0]);
        $this->assertSame('00:15', $slots[1]);
        $this->assertSame('23:45', $slots[95]);
        $this->assertNotContains('24:00', $slots);
        $this->assertNotContains('10:07', $slots);
    }

    #[Test]
    public function it_returns_all_slots_when_start_time_is_empty(): void
    {
        $this->assertSame(BookingTimeSlots::all(), BookingTimeSlots::validEndTimes(null));
        $this->assertSame(BookingTimeSlots::all(), BookingTimeSlots::validEndTimes(''));
    }

    #[Test]
    public function it_hides_end_times_earlier_than_or_equal_to_the_start(): void
    {
        $valid = BookingTimeSlots::validEndTimes('10:00');

        $this->assertNotContains('09:45', $valid);
        $this->assertNotContains('10:00', $valid);
        $this->assertSame('10:15', $valid[0]);
        $this->assertContains('11:00', $valid);
        $this->assertContains('23:45', $valid);
        $this->assertCount(55, $valid);
    }

    #[Test]
    public function it_returns_no_end_times_when_start_is_the_last_slot(): void
    {
        $this->assertSame([], BookingTimeSlots::validEndTimes('23:45'));
    }

    #[Test]
    #[DataProvider('ensureValidEndTimeProvider')]
    public function it_ensures_the_end_time_stays_after_the_start(
        string $startTime,
        ?string $endTime,
        string $expected,
    ): void {
        $this->assertSame($expected, BookingTimeSlots::ensureValidEndTime($startTime, $endTime));
    }

    public static function ensureValidEndTimeProvider(): array
    {
        return [
            'keeps a later end time' => ['10:00', '12:00', '12:00'],
            'replaces an earlier end with start plus one hour' => ['10:00', '09:00', '11:00'],
            'replaces an equal end with start plus one hour' => ['10:00', '10:00', '11:00'],
            'replaces a missing end with start plus one hour' => ['10:00', null, '11:00'],
            'falls back to the next slot when plus one hour is unavailable' => ['23:00', '22:00', '23:15'],
            'returns empty when no later slot exists' => ['23:45', '00:00', ''],
        ];
    }
}
