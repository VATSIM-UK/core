<?php

declare(strict_types=1);

namespace Tests\Unit\Services;

use App\Models\Atc\Position;
use App\Models\Booking;
use App\Services\BookingService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BookingServiceTest extends TestCase
{
    use DatabaseTransactions;

    private BookingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(BookingService::class);
    }

    #[Test]
    public function it_creates_a_booking(): void
    {
        $position = Position::factory()->create();

        $booking = $this->service->create([
            'position_id' => $position->id,
            'type' => Booking::TYPE_STANDARD,
            'starts_at' => Carbon::tomorrow()->setHour(10),
            'ends_at' => Carbon::tomorrow()->setHour(12),
        ]);

        $this->assertInstanceOf(Booking::class, $booking);
        $this->assertDatabaseHas('bookings', ['id' => $booking->id]);
    }

    #[Test]
    public function it_rejects_overlapping_booking(): void
    {
        $position = Position::factory()->create();

        $this->service->create([
            'position_id' => $position->id,
            'type' => Booking::TYPE_STANDARD,
            'starts_at' => Carbon::tomorrow()->setHour(10),
            'ends_at' => Carbon::tomorrow()->setHour(12),
        ]);

        $this->expectException(\RuntimeException::class);

        $this->service->create([
            'position_id' => $position->id,
            'type' => Booking::TYPE_STANDARD,
            'starts_at' => Carbon::tomorrow()->setHour(11),
            'ends_at' => Carbon::tomorrow()->setHour(13),
        ]);
    }

    #[Test]
    public function it_allows_non_overlapping_booking(): void
    {
        $position = Position::factory()->create();

        $this->service->create([
            'position_id' => $position->id,
            'type' => Booking::TYPE_STANDARD,
            'starts_at' => Carbon::tomorrow()->setHour(10),
            'ends_at' => Carbon::tomorrow()->setHour(12),
        ]);

        $booking = $this->service->create([
            'position_id' => $position->id,
            'type' => Booking::TYPE_STANDARD,
            'starts_at' => Carbon::tomorrow()->setHour(13),
            'ends_at' => Carbon::tomorrow()->setHour(15),
        ]);

        $this->assertInstanceOf(Booking::class, $booking);
    }

    #[Test]
    public function it_allows_updating_without_overlap_check_when_times_unchanged(): void
    {
        $position = Position::factory()->create();
        $booking = Booking::factory()->create([
            'position_id' => $position->id,
            'notes' => 'Original',
        ]);

        $updated = $this->service->update($booking, ['notes' => 'Updated']);

        $this->assertEquals('Updated', $updated->notes);
    }

    #[Test]
    public function it_rejects_start_after_end(): void
    {
        $position = Position::factory()->create();

        $this->expectException(\InvalidArgumentException::class);

        $this->service->create([
            'position_id' => $position->id,
            'type' => Booking::TYPE_STANDARD,
            'starts_at' => Carbon::tomorrow()->setHour(12),
            'ends_at' => Carbon::tomorrow()->setHour(10),
        ]);
    }

    #[Test]
    public function is_position_available_returns_correctly(): void
    {
        $position = Position::factory()->create();

        $this->assertTrue(
            $this->service->isPositionAvailable(
                Carbon::tomorrow()->setHour(10),
                Carbon::tomorrow()->setHour(12),
                $position->id
            )
        );

        Booking::factory()->create([
            'position_id' => $position->id,
            'starts_at' => Carbon::tomorrow()->setHour(10),
            'ends_at' => Carbon::tomorrow()->setHour(12),
        ]);

        $this->assertFalse(
            $this->service->isPositionAvailable(
                Carbon::tomorrow()->setHour(11),
                Carbon::tomorrow()->setHour(13),
                $position->id
            )
        );
    }

    #[Test]
    public function it_deletes_a_booking(): void
    {
        $booking = Booking::factory()->create();

        $this->service->delete($booking);

        $this->assertModelMissing($booking);
    }
}
