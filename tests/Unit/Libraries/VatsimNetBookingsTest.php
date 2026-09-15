<?php

declare(strict_types=1);

namespace Tests\Unit\Libraries;

use App\Libraries\VatsimNetBookings;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class VatsimNetBookingsTest extends TestCase
{
    private VatsimNetBookings $library;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.vatsim-net.bookings.url' => 'https://atc-bookings.vatsim.net/api']);
        config(['services.vatsim-net.bookings.key' => 'test-key']);

        $this->library = app(VatsimNetBookings::class);
    }

    #[Test]
    public function it_creates_a_booking_and_returns_the_remote_id(): void
    {
        Http::fake(['atc-bookings.vatsim.net/*' => Http::response(['id' => 123], 201)]);

        $id = $this->library->create([
            'callsign' => 'LON_CTR',
            'cid' => 1240411,
            'type' => 'booking',
            'start' => '2026-01-01 12:00:00',
            'end' => '2026-01-01 14:00:00',
        ]);

        $this->assertSame(123, $id);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://atc-bookings.vatsim.net/api/booking'
                && $request->method() === 'POST'
                && $request->hasHeader('Authorization', 'Bearer test-key')
                && $request['callsign'] === 'LON_CTR'
                && $request['cid'] === 1240411;
        });
    }

    #[Test]
    public function it_updates_a_booking(): void
    {
        Http::fake(['atc-bookings.vatsim.net/*' => Http::response(['id' => 123], 200)]);

        $this->library->update(123, ['callsign' => 'LON_CTR', 'cid' => 1240411, 'type' => 'booking', 'start' => '2026-01-01 12:00:00', 'end' => '2026-01-01 14:00:00']);

        Http::assertSent(fn ($request) => $request->url() === 'https://atc-bookings.vatsim.net/api/booking/123' && $request->method() === 'PUT');
    }

    #[Test]
    public function it_deletes_a_booking(): void
    {
        Http::fake(['atc-bookings.vatsim.net/*' => Http::response('', 204)]);

        $this->library->delete(123);

        Http::assertSent(fn ($request) => $request->url() === 'https://atc-bookings.vatsim.net/api/booking/123' && $request->method() === 'DELETE');
    }

    #[Test]
    public function it_throws_on_a_failed_response(): void
    {
        Http::fake(['atc-bookings.vatsim.net/*' => Http::response(['message' => 'nope'], 422)]);

        $this->expectException(RequestException::class);

        $this->library->create(['callsign' => 'LON_CTR', 'cid' => 1, 'type' => 'booking', 'start' => 'x', 'end' => 'y']);
    }

    #[Test]
    public function it_throws_when_create_returns_no_valid_id(): void
    {
        Http::fake(['atc-bookings.vatsim.net/*' => Http::response([], 201)]);

        $this->expectException(\RuntimeException::class);

        $this->library->create(['callsign' => 'LON_CTR', 'cid' => 1, 'type' => 'booking', 'start' => 'x', 'end' => 'y']);
    }
}
