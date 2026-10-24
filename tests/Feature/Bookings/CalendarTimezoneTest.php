<?php

declare(strict_types=1);

namespace Tests\Feature\Bookings;

use App\Livewire\Bookings\Calendar;
use App\Models\Atc\Position;
use App\Models\Booking;
use App\Models\Cts\Booking as CtsBooking;
use App\Models\Cts\Member as CtsMember;
use App\Models\Events\Event;
use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use App\Models\Mship\State;
use App\Models\Roster;
use App\Repositories\Cts\BookingRepository;
use App\Repositories\Events\EventRepository;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CalendarTimezoneTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function core_bookings_are_placed_on_the_local_day(): void
    {
        $booking = Booking::factory()->create([
            'starts_at' => Carbon::parse('2026-08-01 23:30:00', 'UTC'),
            'ends_at' => Carbon::parse('2026-08-02 01:00:00', 'UTC'),
        ]);

        $repository = app(BookingRepository::class);

        $this->assertNull($repository->getBookings(Carbon::parse('2026-08-01'), 'Europe/Prague')->firstWhere('id', (string) $booking->id));

        $local = $repository->getBookings(Carbon::parse('2026-08-02'), 'Europe/Prague')->firstWhere('id', (string) $booking->id);

        $this->assertNotNull($local);
        $this->assertSame('2026-08-02', $local->date);
        $this->assertSame('01:30', $local->from);
        $this->assertSame('03:00', $local->to);
    }

    #[Test]
    public function cts_only_bookings_are_placed_on_the_local_day(): void
    {
        $member = Account::factory()->create();
        $ctsMember = CtsMember::factory()->forAccount($member)->create();

        $cts = CtsBooking::factory()->create([
            'position' => 'EGXX_FSS',
            'member_id' => $ctsMember->id,
            'type' => 'BK',
            'date' => '2026-08-01',
            'from' => '23:00:00',
            'to' => '23:45:00',
        ]);

        $repository = app(BookingRepository::class);

        $this->assertNull($repository->getBookings(Carbon::parse('2026-08-01'), 'Europe/Prague')->firstWhere('cts_booking_id', (int) $cts->id));

        $local = $repository->getBookings(Carbon::parse('2026-08-02'), 'Europe/Prague')->firstWhere('cts_booking_id', (int) $cts->id);

        $this->assertNotNull($local);
        $this->assertSame('01:00', $local->from);
        $this->assertSame('01:45', $local->to);
    }

    #[Test]
    public function upcoming_member_bookings_skip_cts_bookings_before_the_local_day(): void
    {
        $this->travelTo(Carbon::parse('2026-08-01 23:30:00', 'UTC'));

        $member = Account::factory()->create();
        $ctsMember = CtsMember::factory()->forAccount($member)->create();

        $past = CtsBooking::factory()->create([
            'position' => 'EGXX_FSS',
            'member_id' => $ctsMember->id,
            'type' => 'BK',
            'date' => '2026-08-01',
            'from' => '18:00:00',
            'to' => '19:00:00',
        ]);

        $upcoming = app(BookingRepository::class)->getMemberUpcomingBookings($member, 'Europe/Prague');

        $this->assertNull($upcoming->firstWhere('cts_booking_id', (int) $past->id));
    }

    #[Test]
    public function events_are_placed_on_the_local_day(): void
    {
        Event::factory()->create([
            'start' => Carbon::parse('2026-08-01 23:00:00', 'UTC'),
            'end' => Carbon::parse('2026-08-02 02:00:00', 'UTC'),
            'published_at' => now(),
        ]);

        $repository = app(EventRepository::class);

        $this->assertCount(0, $repository->getEventsForDate(Carbon::parse('2026-08-01'), 'Europe/Prague'));

        $event = $repository->getEventsForDate(Carbon::parse('2026-08-02'), 'Europe/Prague')->first();

        $this->assertSame('2026-08-02', $event->date);
        $this->assertSame('01:00', $event->from);
        $this->assertSame('04:00', $event->to);
    }

    #[Test]
    public function it_defaults_to_utc_until_a_timezone_is_chosen(): void
    {
        Livewire::test(Calendar::class)
            ->assertSet('timezone', 'UTC');
    }

    #[Test]
    public function it_stores_the_chosen_timezone_in_the_session(): void
    {
        Livewire::test(Calendar::class)
            ->call('setTimezone', 'Europe/Prague')
            ->assertSet('timezone', 'Europe/Prague');

        $this->assertSame('Europe/Prague', session('bookings.timezone'));

        Livewire::test(Calendar::class)
            ->assertSet('timezone', 'Europe/Prague');
    }

    #[Test]
    public function it_falls_back_to_utc_for_a_legacy_timezone_name(): void
    {
        Livewire::test(Calendar::class)
            ->call('setTimezone', 'Europe/Prague')
            ->call('setTimezone', 'Asia/Calcutta')
            ->assertSet('timezone', 'UTC');

        $this->assertSame('UTC', session('bookings.timezone'));
    }

    #[Test]
    public function it_falls_back_to_utc_for_an_unknown_timezone(): void
    {
        Livewire::test(Calendar::class)
            ->call('setTimezone', 'Mars/Olympus_Mons')
            ->assertSet('timezone', 'UTC');

        $this->assertSame('UTC', session('bookings.timezone'));
    }

    #[Test]
    public function the_day_view_shows_bookings_in_the_chosen_timezone(): void
    {
        $this->travelTo(Carbon::parse('2026-08-01 12:00:00', 'UTC'));

        $position = Position::factory()->create(['callsign' => 'EGLL_TWR']);
        Booking::factory()->create([
            'position_id' => $position->id,
            'starts_at' => Carbon::parse('2026-08-01 23:30:00', 'UTC'),
            'ends_at' => Carbon::parse('2026-08-02 01:00:00', 'UTC'),
        ]);

        session(['bookings.timezone' => 'Europe/Prague']);

        Livewire::test(Calendar::class, ['year' => 2026, 'month' => 8])
            ->assertDontSee('EGLL_TWR')
            ->call('jumpToDate', '2026-08-02')
            ->assertSee('EGLL_TWR')
            ->assertSee('01:30');
    }

    #[Test]
    public function today_follows_the_chosen_timezone(): void
    {
        $this->travelTo(Carbon::parse('2026-08-01 23:00:00', 'UTC'));

        session(['bookings.timezone' => 'Europe/Prague']);

        Livewire::test(Calendar::class)
            ->assertSet('selectedDate', Carbon::parse('2026-08-02'));
    }

    #[Test]
    public function a_booking_link_opens_the_bookings_local_day(): void
    {
        $booking = Booking::factory()->create([
            'starts_at' => Carbon::parse('2026-08-01 23:30:00', 'UTC'),
            'ends_at' => Carbon::parse('2026-08-02 01:00:00', 'UTC'),
        ]);

        session(['bookings.timezone' => 'Europe/Prague']);

        Livewire::withQueryParams(['booking_id' => (string) $booking->id])
            ->test(Calendar::class)
            ->assertSet('selectedDate', Carbon::parse('2026-08-02'));
    }

    #[Test]
    public function switching_timezone_keeps_a_linked_booking_in_view(): void
    {
        $booking = Booking::factory()->create([
            'starts_at' => Carbon::parse('2026-08-01 23:30:00', 'UTC'),
            'ends_at' => Carbon::parse('2026-08-02 01:00:00', 'UTC'),
        ]);

        Livewire::withQueryParams(['booking_id' => (string) $booking->id])
            ->test(Calendar::class)
            ->assertSet('selectedDate', Carbon::parse('2026-08-01'))
            ->call('setTimezone', 'Europe/Prague')
            ->assertSet('selectedDate', Carbon::parse('2026-08-02'))
            ->assertDispatched('scroll-to-booking', source: 'core', id: $booking->id, ctsBookingId: null, instant: true);
    }

    #[Test]
    public function bookings_are_entered_in_the_chosen_timezone(): void
    {
        $this->travelTo(Carbon::parse('2026-08-01 12:00:00', 'UTC'));

        $member = Account::factory()->withQualification()->create();
        $member->qualifications()->sync([Qualification::factory()->atc()->create(['vatsim' => 5])->id]);
        $member = $member->fresh();
        $member->addState(State::findByCode('DIVISION'));
        Roster::create(['account_id' => $member->id]);

        $position = Position::factory()->create(['type' => Position::TYPE_ENROUTE]);

        session(['bookings.timezone' => 'America/New_York']);

        Livewire::actingAs($member)
            ->test(Calendar::class)
            ->call('createBooking', [
                'starts_at' => '2026-08-02T10:00',
                'ends_at' => '2026-08-02T12:00',
                'position_id' => (string) $position->id,
            ])
            ->assertDispatched('booking-created');

        $booking = Booking::where('member_id', $member->id)->sole();

        $this->assertSame('2026-08-02 14:00:00', $booking->starts_at->utc()->format('Y-m-d H:i:s'));
        $this->assertSame('2026-08-02 16:00:00', $booking->ends_at->utc()->format('Y-m-d H:i:s'));
    }
}
