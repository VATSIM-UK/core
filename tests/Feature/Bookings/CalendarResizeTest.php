<?php

declare(strict_types=1);

namespace Tests\Feature\Bookings;

use App\Livewire\Bookings\Calendar;
use App\Models\Atc\Position;
use App\Models\Booking;
use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use App\Models\Mship\State;
use App\Models\NetworkData\Atc;
use App\Models\Roster;
use Carbon\Carbon;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CalendarResizeTest extends TestCase
{
    private Account $member;

    private Booking $booking;

    private Carbon $day;

    protected function setUp(): void
    {
        parent::setUp();

        $this->day = Carbon::tomorrow();

        $member = Account::factory()->withQualification()->create();
        $member->qualifications()->sync([Qualification::factory()->atc()->create(['vatsim' => 5])->id]);
        $member->addState(State::findByCode('DIVISION'));
        Roster::create(['account_id' => $member->id]);
        $this->member = $member->fresh();

        $this->booking = Booking::factory()->create([
            'member_id' => $this->member->id,
            'position_id' => Position::factory()->create(['type' => Position::TYPE_ENROUTE])->id,
            'starts_at' => $this->day->copy()->setTime(10, 0),
            'ends_at' => $this->day->copy()->setTime(12, 0),
        ]);
    }

    #[Test]
    public function it_extends_the_end_of_a_booking(): void
    {
        $this->resize('10:00', '13:30')->assertNotDispatched('booking-resize-failed');

        $this->assertTrue($this->booking->fresh()->ends_at->eq($this->day->copy()->setTime(13, 30)));
    }

    #[Test]
    public function it_moves_the_start_of_a_booking_earlier(): void
    {
        $this->resize('08:45', '12:00')->assertNotDispatched('booking-resize-failed');

        $this->assertTrue($this->booking->fresh()->starts_at->eq($this->day->copy()->setTime(8, 45)));
    }

    #[Test]
    public function it_extends_a_booking_to_midnight(): void
    {
        Livewire::actingAs($this->member)
            ->test(Calendar::class)
            ->call('resizeBooking', $this->booking->id, $this->localTime('10:00'), $this->day->copy()->addDay()->format('Y-m-d').'T00:00')
            ->assertNotDispatched('booking-resize-failed');

        $this->assertTrue($this->booking->fresh()->ends_at->eq($this->day->copy()->addDay()));
    }

    #[Test]
    public function it_does_not_resize_another_members_booking(): void
    {
        Livewire::actingAs(Account::factory()->create())
            ->test(Calendar::class)
            ->call('resizeBooking', $this->booking->id, $this->localTime('10:00'), $this->localTime('14:00'))
            ->assertDispatched('booking-resize-failed', message: 'You can only change your own bookings.');

        $this->assertTrue($this->booking->fresh()->ends_at->eq($this->day->copy()->setTime(12, 0)));
    }

    #[Test]
    public function it_does_not_resize_for_guests(): void
    {
        Livewire::test(Calendar::class)
            ->call('resizeBooking', $this->booking->id, $this->localTime('10:00'), $this->localTime('14:00'))
            ->assertDispatched('booking-resize-failed');
    }

    #[Test]
    public function it_does_not_move_the_start_of_a_booking_that_has_started(): void
    {
        Carbon::setTestNow($this->day->copy()->setTime(10, 30));

        $this->resize('09:30', '12:00')
            ->assertDispatched('booking-resize-failed', message: 'You cannot move the start of a booking that has already started.');
    }

    #[Test]
    public function it_extends_the_end_of_a_running_booking_without_the_member_being_online(): void
    {
        Carbon::setTestNow($this->day->copy()->setTime(10, 30));

        $this->resize('10:00', '13:00')->assertNotDispatched('booking-resize-failed');

        $this->assertTrue($this->booking->fresh()->ends_at->eq($this->day->copy()->setTime(13, 0)));
    }

    #[Test]
    public function it_still_requires_notice_when_moving_the_start_of_an_imminent_booking(): void
    {
        Carbon::setTestNow($this->day->copy()->setTime(9, 0));

        $this->resize('09:30', '12:00')
            ->assertDispatched('booking-resize-failed', message: 'You must be currently controlling this position to book less than 2 hours in advance.');
    }

    #[Test]
    public function it_does_not_resize_a_booking_that_has_ended(): void
    {
        Carbon::setTestNow($this->day->copy()->setTime(13, 0));

        $this->resize('10:00', '14:00')
            ->assertDispatched('booking-resize-failed', message: 'You cannot change a booking that has already ended.');
    }

    #[Test]
    public function it_rejects_times_off_fifteen_minute_boundaries(): void
    {
        $this->resize('10:00', '12:10')
            ->assertDispatched('booking-resize-failed', message: 'Start and end times must be on 15-minute boundaries.');
    }

    #[Test]
    public function it_rejects_an_overlap_with_another_booking_on_the_position(): void
    {
        Booking::factory()->create([
            'position_id' => $this->booking->position_id,
            'starts_at' => $this->day->copy()->setTime(12, 0),
            'ends_at' => $this->day->copy()->setTime(14, 0),
        ]);

        $this->resize('10:00', '13:00')->assertDispatched('booking-resize-failed');

        $this->assertTrue($this->booking->fresh()->ends_at->eq($this->day->copy()->setTime(12, 0)));
    }

    #[Test]
    public function it_marks_only_the_members_own_future_bookings_as_resizable(): void
    {
        Booking::factory()->create([
            'starts_at' => $this->day->copy()->setTime(15, 0),
            'ends_at' => $this->day->copy()->setTime(16, 0),
        ]);

        $bookings = collect(
            Livewire::actingAs($this->member)
                ->test(Calendar::class)
                ->call('jumpToDate', $this->day->toDateString())
                ->get('timelinePositions')
        )
            ->flatMap(fn (array $item): array => $item['positions'] ?? [$item])
            ->flatMap(fn (array $position): array => $position['bookings'] ?? [])
            ->keyBy('id');

        $this->assertTrue($bookings[(string) $this->booking->id]['can_resize_start']);
        $this->assertTrue($bookings[(string) $this->booking->id]['can_resize_end']);

        $other = $bookings->first(fn (array $booking): bool => $booking['id'] !== (string) $this->booking->id);
        $this->assertFalse($other['can_resize_start']);
        $this->assertFalse($other['can_resize_end']);
    }

    #[Test]
    public function it_gives_the_timeline_the_notice_cutoff_and_the_members_live_callsign(): void
    {
        Carbon::setTestNow(Carbon::today()->setTime(9, 30));

        Atc::create([
            'account_id' => $this->member->id,
            'callsign' => 'EGLL_TWR',
            'connected_at' => Carbon::now()->subHour(),
            'qualification_id' => 1,
            'facility_type' => 4,
        ]);

        Livewire::actingAs($this->member)
            ->test(Calendar::class)
            ->assertViewHas('noticeCutoffMinutes', 11 * 60 + 30)
            ->assertViewHas('controllingCallsign', 'EGLL_TWR')
            ->call('jumpToDate', $this->day->toDateString())
            ->assertViewHas('noticeCutoffMinutes', -(12 * 60 + 30));
    }

    private function resize(string $from, string $to)
    {
        return Livewire::actingAs($this->member)
            ->test(Calendar::class)
            ->call('resizeBooking', $this->booking->id, $this->localTime($from), $this->localTime($to));
    }

    private function localTime(string $time): string
    {
        return $this->day->format('Y-m-d').'T'.$time;
    }
}
