<?php

declare(strict_types=1);

namespace Tests\Unit\Jobs\Booking;

use App\Jobs\Booking\SyncToVatsimNet;
use App\Models\Booking;
use App\Services\Bookings\VatsimNetBookingSyncService;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class SyncToVatsimNetTest extends TestCase
{
    #[Test]
    public function it_skips_when_the_booking_no_longer_exists(): void
    {
        Http::fake();

        $job = new SyncToVatsimNet(999999);

        $job->handle(app(VatsimNetBookingSyncService::class));

        Http::assertNothingSent();
    }

    #[Test]
    public function it_syncs_an_existing_booking(): void
    {
        Http::fake(['atc-bookings.vatsim.net/*' => Http::response(['id' => 5], 201)]);

        $booking = Booking::factory()->create();

        $job = new SyncToVatsimNet($booking->id);

        $job->handle(app(VatsimNetBookingSyncService::class));

        Http::assertSent(fn ($request) => $request->method() === 'POST');
    }

    #[Test]
    public function it_deletes_the_remote_booking_when_marked_deleted(): void
    {
        Http::fake(['atc-bookings.vatsim.net/*' => Http::response('', 204)]);

        $job = new SyncToVatsimNet(123, true, 456);

        $job->handle(app(VatsimNetBookingSyncService::class));

        Http::assertSent(fn ($request) => $request->url() === 'https://atc-bookings.vatsim.net/api/booking/456' && $request->method() === 'DELETE');
    }
}
