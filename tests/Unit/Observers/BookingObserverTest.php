<?php

declare(strict_types=1);

namespace Tests\Unit\Observers;

use App\Jobs\Booking\SyncToVatsimNet;
use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Support\Facades\Bus;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class BookingObserverTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['services.vatsim-net.bookings.key' => 'test-key']);
    }

    #[Test]
    public function it_dispatches_when_a_booking_is_created(): void
    {
        Bus::fake();

        Booking::factory()->create();

        Bus::assertDispatched(SyncToVatsimNet::class);
    }

    #[Test]
    public function it_does_not_dispatch_when_no_api_key_is_configured(): void
    {
        config(['services.vatsim-net.bookings.key' => '']);

        Bus::fake();

        Booking::factory()->create();

        Bus::assertNotDispatched(SyncToVatsimNet::class);
    }

    #[Test]
    public function it_does_not_dispatch_for_event_bookings(): void
    {
        Bus::fake();

        Booking::factory()->forEvent()->create();

        Bus::assertNotDispatched(SyncToVatsimNet::class);
    }

    #[Test]
    public function it_dispatches_when_a_relevant_field_changes(): void
    {
        Bus::fake();
        $booking = Booking::factory()->create();

        Bus::fake();

        $booking->update(['starts_at' => Carbon::tomorrow()->setHour(15)]);

        Bus::assertDispatched(SyncToVatsimNet::class);
    }

    #[Test]
    public function it_does_not_dispatch_when_only_an_irrelevant_field_changes(): void
    {
        Bus::fake();
        $booking = Booking::factory()->create();

        Bus::fake();

        $booking->update(['vatsim_net_booking_id' => 42]);

        Bus::assertNotDispatched(SyncToVatsimNet::class);
    }

    #[Test]
    public function it_dispatches_when_a_booking_is_deleted(): void
    {
        Bus::fake();
        $booking = Booking::factory()->create(['vatsim_net_booking_id' => 77]);

        Bus::fake();

        $booking->delete();

        Bus::assertDispatched(SyncToVatsimNet::class);
    }
}
