<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\Booking;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BookingVatsimNetIdTest extends TestCase
{
    #[Test]
    public function it_persists_and_casts_the_vatsim_net_booking_id(): void
    {
        $booking = Booking::factory()->create(['vatsim_net_booking_id' => 42]);

        $this->assertSame(42, $booking->vatsim_net_booking_id);
        $this->assertDatabaseHas('bookings', ['id' => $booking->id, 'vatsim_net_booking_id' => 42]);
    }

    #[Test]
    public function it_defaults_the_vatsim_net_booking_id_to_null(): void
    {
        $booking = Booking::factory()->create();

        $this->assertNull($booking->vatsim_net_booking_id);
    }
}
