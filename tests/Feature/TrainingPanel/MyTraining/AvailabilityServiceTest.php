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

        Carbon::setTestNow(Carbon::create(2026, 4, 18, 12, 0, 0, 'UTC'));
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
}
