<?php

declare(strict_types=1);

namespace Tests\Unit\Support\Calendar;

use App\Models\Cts\Session;
use App\Support\Calendar\CalendarSequence;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CalendarSequenceTest extends TestCase
{
    #[Test]
    public function it_is_zero_for_a_session_that_was_never_taken(): void
    {
        $this->assertSame(0, CalendarSequence::forSession($this->makeSession(null)));
    }

    #[Test]
    public function it_increases_when_the_session_is_rescheduled(): void
    {
        $accepted = CalendarSequence::forSession($this->makeSession('2026-05-01 10:00:00'));
        $rescheduled = CalendarSequence::forSession($this->makeSession('2026-05-02 09:30:00'));

        $this->assertGreaterThan(0, $accepted);
        $this->assertGreaterThan($accepted, $rescheduled);
    }

    #[Test]
    public function it_fits_in_a_32_bit_integer(): void
    {
        $this->assertLessThan(2 ** 31, CalendarSequence::forSession($this->makeSession('2080-01-01 00:00:00')));
    }

    private function makeSession(?string $takenTime): Session
    {
        return (new Session)->forceFill(['taken_time' => $takenTime]);
    }
}
