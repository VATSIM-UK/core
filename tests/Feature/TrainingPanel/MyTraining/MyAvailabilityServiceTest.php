<?php

namespace Tests\Unit\Services\Training;

use App\Models\Cts\Availability;
use App\Models\Cts\Member;
use App\Services\Training\AvailabilityService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class MyAvailabilityServiceTest extends TestCase
{
    use DatabaseTransactions;

    protected AvailabilityService $service;

    protected Member $member;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new AvailabilityService;
        $this->member = Member::factory()->create();

        Carbon::setTestNow(Carbon::create(2026, 4, 18, 12, 0, 0));
    }

    #[Test]
    public function it_returns_false_if_start_time_is_after_end_time()
    {
        [$valid, $message] = $this->service->isSlotValid(
            $this->member->id,
            '2026-04-19',
            '21:00',
            '18:00'
        );

        $this->assertFalse($valid);
        $this->assertEquals('The end time must be after the start time.', $message);
    }

    #[Test]
    public function it_returns_false_if_slot_is_in_the_past()
    {
        [$valid, $message] = $this->service->isSlotValid(
            $this->member->id,
            '2026-04-17',
            '12:00',
            '14:00'
        );

        $this->assertFalse($valid);
        $this->assertEquals('Availability cannot be in the past.', $message);
    }

    #[Test]
    public function it_detects_overlapping_slots()
    {
        Availability::factory()->create([
            'student_id' => $this->member->id,
            'date' => '2026-04-19',
            'from' => '18:00',
            'to' => '20:00',
            'type' => 'S',
        ]);

        [$valid, $message] = $this->service->isSlotValid(
            $this->member->id,
            '2026-04-19',
            '19:00',
            '21:00'
        );

        $this->assertFalse($valid);
        $this->assertEquals('This slot overlaps with an existing entry.', $message);
    }

    #[Test]
    public function it_allows_perfectly_adjacent_slots()
    {
        Availability::factory()->create([
            'student_id' => $this->member->id,
            'date' => '2026-04-19',
            'from' => '18:00',
            'to' => '19:00',
            'type' => 'S',
        ]);

        [$valid] = $this->service->isSlotValid(
            $this->member->id,
            '2026-04-19',
            '19:00',
            '20:00'
        );

        $this->assertTrue($valid);
    }

    #[Test]
    public function it_validates_a_completely_unique_slot()
    {
        [$valid] = $this->service->isSlotValid(
            $this->member->id,
            '2026-04-20',
            '09:00',
            '10:00'
        );

        $this->assertTrue($valid);
    }
}
