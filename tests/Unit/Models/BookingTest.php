<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Atc\Position;
use App\Models\Booking;
use App\Models\Mship\Account;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BookingTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_can_create_a_standard_booking(): void
    {
        $position = Position::factory()->create();
        $member = Account::factory()->create();

        $booking = Booking::create([
            'position_id' => $position->id,
            'member_id' => $member->id,
            'type' => Booking::TYPE_STANDARD,
            'starts_at' => Carbon::tomorrow()->setHour(10),
            'ends_at' => Carbon::tomorrow()->setHour(12),
        ]);

        $this->assertInstanceOf(Booking::class, $booking);
        $this->assertEquals($position->id, $booking->position_id);
        $this->assertEquals($member->id, $booking->member_id);
        $this->assertEquals(Booking::TYPE_STANDARD, $booking->type);
    }

    #[Test]
    public function it_can_create_a_booking_without_member(): void
    {
        $position = Position::factory()->create();

        $booking = Booking::create([
            'position_id' => $position->id,
            'type' => Booking::TYPE_EVENT,
            'starts_at' => Carbon::tomorrow()->setHour(10),
            'ends_at' => Carbon::tomorrow()->setHour(12),
        ]);

        $this->assertNull($booking->member_id);
    }

    #[Test]
    public function overlapping_scope_detects_conflicts(): void
    {
        $position = Position::factory()->create();
        $member = Account::factory()->create();

        Booking::create([
            'position_id' => $position->id,
            'member_id' => $member->id,
            'type' => Booking::TYPE_STANDARD,
            'starts_at' => Carbon::tomorrow()->setHour(10),
            'ends_at' => Carbon::tomorrow()->setHour(12),
        ]);

        $overlapping = Booking::overlapping(
            Carbon::tomorrow()->setHour(11),
            Carbon::tomorrow()->setHour(13),
            $position->id
        )->exists();

        $nonOverlapping = Booking::overlapping(
            Carbon::tomorrow()->setHour(13),
            Carbon::tomorrow()->setHour(15),
            $position->id
        )->exists();

        $this->assertTrue($overlapping);
        $this->assertFalse($nonOverlapping);
    }

    #[Test]
    public function it_belongs_to_position(): void
    {
        $position = Position::factory()->create();
        $booking = Booking::factory()->create(['position_id' => $position->id]);

        $this->assertTrue($booking->position->is($position));
    }

    #[Test]
    public function it_belongs_to_member(): void
    {
        $member = Account::factory()->create();
        $booking = Booking::factory()->create(['member_id' => $member->id]);

        $this->assertTrue($booking->member->is($member));
    }

    #[Test]
    public function it_casts_dates(): void
    {
        $booking = Booking::factory()->create();

        $this->assertInstanceOf(Carbon::class, $booking->starts_at);
        $this->assertInstanceOf(Carbon::class, $booking->ends_at);
    }

    #[Test]
    public function scope_of_type_filters_correctly(): void
    {
        Booking::factory()->create(['type' => Booking::TYPE_STANDARD]);
        Booking::factory()->forExam()->create();

        $standards = Booking::ofType(Booking::TYPE_STANDARD)->get();
        $exams = Booking::ofType(Booking::TYPE_EXAM)->get();

        $this->assertCount(1, $standards);
        $this->assertCount(1, $exams);
    }
}
