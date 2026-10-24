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
use App\Repositories\Cts\BookingRepository;
use App\Repositories\Events\EventRepository;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CalendarMidnightTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function a_booking_over_midnight_is_split_across_both_days(): void
    {
        $booking = $this->overnightBooking();
        $repository = app(BookingRepository::class);

        $first = $repository->getBookings(Carbon::parse('2026-08-01'), 'UTC', splitAcrossDays: true)->firstWhere('id', (string) $booking->id);
        $second = $repository->getBookings(Carbon::parse('2026-08-02'), 'UTC', splitAcrossDays: true)->firstWhere('id', (string) $booking->id);

        $this->assertSame(['2026-08-01', '23:30', '24:00', false, true], [$first->date, $first->from, $first->to, $first->continues_before, $first->continues_after]);
        $this->assertSame(['2026-08-02', '00:00', '01:00', true, false], [$second->date, $second->from, $second->to, $second->continues_before, $second->continues_after]);

        foreach ([$first, $second] as $part) {
            $this->assertSame(['2026-08-01', '23:30', '2026-08-02', '01:00'], [$part->starts_on, $part->time_from, $part->ends_on, $part->time_to]);
        }
    }

    #[Test]
    public function bookings_are_only_listed_on_their_start_day_unless_split(): void
    {
        $booking = $this->overnightBooking();
        $repository = app(BookingRepository::class);

        $this->assertNull($repository->getBookings(Carbon::parse('2026-08-02'))->firstWhere('id', (string) $booking->id));

        $first = $repository->getBookings(Carbon::parse('2026-08-01'))->firstWhere('id', (string) $booking->id);

        $this->assertSame(['23:30', '01:00'], [$first->from, $first->to]);
    }

    #[Test]
    public function a_booking_ending_at_midnight_does_not_spill_into_the_next_day(): void
    {
        $booking = Booking::factory()->create([
            'starts_at' => Carbon::parse('2026-08-01 22:00:00', 'UTC'),
            'ends_at' => Carbon::parse('2026-08-02 00:00:00', 'UTC'),
        ]);
        $repository = app(BookingRepository::class);

        $this->assertNull($repository->getBookings(Carbon::parse('2026-08-02'), 'UTC', splitAcrossDays: true)->firstWhere('id', (string) $booking->id));

        $first = $repository->getBookings(Carbon::parse('2026-08-01'), 'UTC', splitAcrossDays: true)->firstWhere('id', (string) $booking->id);

        $this->assertSame(['22:00', '24:00', false], [$first->from, $first->to, $first->continues_after]);
    }

    #[Test]
    public function a_cts_only_booking_over_midnight_is_split(): void
    {
        $ctsMember = CtsMember::factory()->forAccount(Account::factory()->create())->create();
        $cts = CtsBooking::factory()->create([
            'position' => 'EGXX_FSS',
            'member_id' => $ctsMember->id,
            'type' => 'BK',
            'date' => '2026-08-01',
            'from' => '23:00:00',
            'to' => '01:00:00',
        ]);

        $second = app(BookingRepository::class)
            ->getBookings(Carbon::parse('2026-08-02'), 'UTC', splitAcrossDays: true)
            ->firstWhere('cts_booking_id', (int) $cts->id);

        $this->assertSame(['00:00', '01:00', true], [$second->from, $second->to, $second->continues_before]);
    }

    #[Test]
    public function an_event_over_midnight_is_split(): void
    {
        Event::factory()->create([
            'start' => Carbon::parse('2026-08-01 22:00:00', 'UTC'),
            'end' => Carbon::parse('2026-08-02 02:00:00', 'UTC'),
            'published_at' => now(),
        ]);

        $repository = app(EventRepository::class);
        $first = $repository->getEventsForDate(Carbon::parse('2026-08-01'))->sole();
        $second = $repository->getEventsForDate(Carbon::parse('2026-08-02'))->sole();

        $this->assertSame(['22:00', '24:00', true], [$first->from, $first->to, $first->continues_after]);
        $this->assertSame(['00:00', '02:00', true], [$second->from, $second->to, $second->continues_before]);
    }

    #[Test]
    public function the_timeline_draws_each_part_to_the_edge_of_its_day(): void
    {
        $booking = $this->overnightBooking();

        $first = $this->timelineBooking(Livewire::test(Calendar::class, ['year' => 2026, 'month' => 8]), $booking);

        $this->assertSame(1440, $first['endMin']);
        $this->assertSame(100.0, round($first['left_pct'] + $first['width_pct'], 1));
        $this->assertSame(['23:30', '01:00'], [$first['time_from'], $first['time_to']]);

        $second = $this->timelineBooking(Livewire::test(Calendar::class, ['year' => 2026, 'month' => 8])->call('jumpToDate', '2026-08-02'), $booking);

        $this->assertSame([0, 60], [$second['startMin'], $second['endMin']]);
        $this->assertSame(0.0, $second['left_pct']);
    }

    #[Test]
    public function the_week_view_lists_the_booking_on_both_days_with_its_real_times(): void
    {
        $booking = $this->overnightBooking();

        $component = Livewire::withQueryParams(['week' => '31'])
            ->test(Calendar::class, ['year' => 2026])
            ->instance();

        foreach (['2026-08-01', '2026-08-02'] as $day) {
            $block = collect($component->buildWeekDayBlocks($day))->firstWhere('id', $booking->id);

            $this->assertNotNull($block, "Missing on {$day}");
            $this->assertSame(['23:30', '01:00'], [$block['from'], $block['to']]);
        }
    }

    private function overnightBooking(): Booking
    {
        return Booking::factory()->create([
            'position_id' => Position::factory()->create(['callsign' => 'EGLL_TWR'])->id,
            'starts_at' => Carbon::parse('2026-08-01 23:30:00', 'UTC'),
            'ends_at' => Carbon::parse('2026-08-02 01:00:00', 'UTC'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function timelineBooking($component, Booking $booking): array
    {
        return collect($component->get('timelinePositions'))
            ->flatMap(fn (array $row): array => $row['positions'] ?? [$row])
            ->flatMap(fn (array $position): array => $position['bookings'])
            ->firstWhere('id', (string) $booking->id);
    }
}
