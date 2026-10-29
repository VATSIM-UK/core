<?php

namespace Tests\Unit\Models\Training;

use App\Models\Training\Availability;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AvailabilityTest extends TestCase
{
    private function slot(string $startsAt, string $endsAt): Availability
    {
        return new Availability(['starts_at' => $startsAt, 'ends_at' => $endsAt]);
    }

    #[Test]
    public function a_slot_ending_at_midnight_books_up_to_a_quarter_to(): void
    {
        foreach ([['18:00:00', '23:45:00'], ['00:00:00', '23:45:00']] as [$from, $expected]) {
            $slot = $this->slot("2026-11-02 {$from}", '2026-11-03 00:00:00');

            $this->assertSame("2026-11-02 {$expected}", $slot->bookableEndsAt()->toDateTimeString());
            $this->assertFalse($slot->crossesMidnight());
        }
    }

    #[Test]
    public function other_slots_are_booked_to_their_own_end(): void
    {
        $this->assertSame(
            '2026-11-02 21:00:00',
            $this->slot('2026-11-02 18:00:00', '2026-11-02 21:00:00')->bookableEndsAt()->toDateTimeString(),
        );

        $this->assertSame(
            '2026-11-03 02:00:00',
            $this->slot('2026-11-02 22:00:00', '2026-11-03 02:00:00')->bookableEndsAt()->toDateTimeString(),
        );
    }
}
