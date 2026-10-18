<?php

namespace Tests\Unit\Services\Training;

use App\Models\Cts\Availability;
use App\Models\Cts\Member;
use App\Services\Training\AvailabilityService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class AvailabilityServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected AvailabilityService $service;

    protected Member $member;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new AvailabilityService;
        $this->member = Member::factory()->create();

        $this->travelTo(Carbon::create(2026, 4, 18, 12, 0, 0, 'UTC'));
    }

    #[Test]
    public function it_returns_false_if_start_time_is_after_end_time()
    {
        $start = Carbon::parse('2026-04-19 21:00:00');
        $end = Carbon::parse('2026-04-19 18:00:00');

        [$valid, $message] = $this->service->isSlotValid($this->member->id, $start, $end);

        $this->assertFalse($valid);
        $this->assertEquals('The availability end time must be after the start time.', $message);
    }

    #[Test]
    public function it_returns_false_if_slot_is_shorter_than_minimum_duration()
    {
        $start = Carbon::parse('2026-04-19 18:00:00');
        $end = Carbon::parse('2026-04-19 18:30:00');

        [$valid, $message] = $this->service->isSlotValid($this->member->id, $start, $end);

        $this->assertFalse($valid);
        $this->assertEquals(
            'Availability slots must be at least '.AvailabilityService::MINIMUM_SLOT_DURATION_MINUTES.' minutes long.',
            $message
        );
    }

    #[Test]
    public function it_returns_true_if_slot_exactly_meets_minimum_duration()
    {
        $start = Carbon::parse('2026-04-19 18:00:00');
        $end = Carbon::parse('2026-04-19 18:45:00');

        [$valid] = $this->service->isSlotValid($this->member->id, $start, $end);

        $this->assertTrue($valid);
    }

    #[Test]
    public function it_returns_false_if_slot_is_in_the_past()
    {
        $start = Carbon::parse('2026-04-17 12:00:00');
        $end = Carbon::parse('2026-04-17 14:00:00');

        [$valid, $message] = $this->service->isSlotValid($this->member->id, $start, $end);

        $this->assertFalse($valid);
        $this->assertEquals('Availability cannot be in the past.', $message);
    }

    #[Test]
    public function it_detects_overlapping_slots()
    {
        Availability::factory()->create([
            'student_id' => $this->member->id,
            'date' => '2026-04-19',
            'from' => '18:00:00',
            'to' => '20:00:00',
            'type' => 'S',
        ]);

        $start = Carbon::parse('2026-04-19 19:00:00');
        $end = Carbon::parse('2026-04-19 21:00:00');

        [$valid, $message] = $this->service->isSlotValid($this->member->id, $start, $end);

        $this->assertFalse($valid);
        $this->assertEquals('This availability slot overlaps with an existing entry.', $message);
    }

    #[Test]
    public function it_allows_perfectly_adjacent_slots()
    {
        Availability::factory()->create([
            'student_id' => $this->member->id,
            'date' => '2026-04-19',
            'from' => '18:00:00',
            'to' => '19:00:00',
            'type' => 'S',
        ]);

        $start = Carbon::parse('2026-04-19 19:00:00');
        $end = Carbon::parse('2026-04-19 20:00:00');

        [$valid] = $this->service->isSlotValid($this->member->id, $start, $end);

        $this->assertTrue($valid);
    }

    #[Test]
    public function it_validates_correctly_across_timezones()
    {
        /** * Scenario: It is 12:00 UTC.
         * A user in New York (UTC-4) wants to add a slot for 09:00 AM local time.
         * 09:00 AM New York is 13:00 UTC.
         * This SHOULD be valid because 13:00 UTC is in the future.
         */
        $nyStart = Carbon::parse('2026-04-18 09:00:00', 'America/New_York')->utc();
        $nyEnd = Carbon::parse('2026-04-18 11:00:00', 'America/New_York')->utc();

        [$valid] = $this->service->isSlotValid($this->member->id, $nyStart, $nyEnd);

        $this->assertTrue($valid, 'Slot should be valid as the UTC conversion is in the future.');
    }

    #[Test]
    public function it_validates_a_completely_unique_slot()
    {
        $start = Carbon::parse('2026-04-20 09:00:00');
        $end = Carbon::parse('2026-04-20 10:00:00');

        [$valid] = $this->service->isSlotValid($this->member->id, $start, $end);

        $this->assertTrue($valid);
    }

    #[Test]
    public function it_allows_the_last_day_of_the_window(): void
    {
        $this->assertFalse($this->service->isBeyondFutureWindow(Carbon::parse('2026-05-19 09:00', 'UTC')));
    }

    #[Test]
    public function it_refuses_the_day_after_the_window(): void
    {
        $this->assertTrue($this->service->isBeyondFutureWindow(Carbon::parse('2026-05-20 09:00', 'UTC')));
    }

    #[Test]
    public function it_ignores_the_time_of_day_when_comparing(): void
    {
        $this->assertFalse($this->service->isBeyondFutureWindow(Carbon::parse('2026-05-19 23:59', 'UTC')));
        $this->assertTrue($this->service->isBeyondFutureWindow(Carbon::parse('2026-05-20 00:00', 'UTC')));
    }

    #[Test]
    public function it_reports_the_last_allowed_date(): void
    {
        $this->assertSame('2026-05-19', AvailabilityService::lastAllowedDate(Carbon::parse('2026-04-18 12:00', 'UTC'))->toDateString());
    }

    #[Test]
    public function it_returns_false_for_a_slot_after_the_window(): void
    {
        [$valid, $message] = $this->service->isSlotValid(
            $this->member->id,
            Carbon::parse('2026-05-20 18:00:00', 'UTC'),
            Carbon::parse('2026-05-20 21:00:00', 'UTC'),
        );

        $this->assertFalse($valid);
        $this->assertEquals('Availability cannot be more than '.AvailabilityService::MAX_FUTURE_DAYS.' days in the future.', $message);
    }

    #[Test]
    public function it_counts_student_availability_within_the_window(): void
    {
        Availability::factory()->forStudent($this->member->id)->create([
            'date' => now()->addDay()->toDateString(),
            'type' => 'S',
        ]);

        $this->assertTrue($this->service->hasAvailabilityWithinWindow($this->member->id));
    }

    #[Test]
    public function it_ignores_non_student_availability_within_the_window(): void
    {
        Availability::factory()->forStudent($this->member->id)->create([
            'date' => now()->addDay()->toDateString(),
            'type' => 'M',
        ]);

        $this->assertFalse($this->service->hasAvailabilityWithinWindow($this->member->id));
    }
}
