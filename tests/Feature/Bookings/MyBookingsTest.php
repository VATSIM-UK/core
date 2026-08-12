<?php

declare(strict_types=1);

namespace Tests\Feature\Bookings;

use App\Livewire\Bookings\Calendar;
use App\Models\Atc\Position;
use App\Models\Booking;
use App\Models\Cts\Booking as CtsBooking;
use App\Models\Cts\Member as CtsMember;
use App\Models\Mship\Account;
use App\Repositories\Cts\BookingRepository;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class MyBookingsTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_lists_a_members_future_core_booking(): void
    {
        $member = Account::factory()->create();
        $position = Position::factory()->create(['callsign' => 'EGKK_APP']);

        Booking::create([
            'position_id' => $position->id,
            'member_id' => $member->id,
            'type' => Booking::TYPE_STANDARD,
            'starts_at' => Carbon::tomorrow()->setHour(10),
            'ends_at' => Carbon::tomorrow()->setHour(12),
        ]);

        $bookings = app(BookingRepository::class)->getMemberUpcomingBookings($member);

        $this->assertCount(1, $bookings);
        $this->assertSame('EGKK_APP', $bookings->first()->position);
        $this->assertSame('10:00', $bookings->first()->from);
        $this->assertSame('12:00', $bookings->first()->to);
    }

    #[Test]
    public function it_lists_a_members_future_cts_booking(): void
    {
        $member = Account::factory()->create();
        $ctsMember = CtsMember::factory()->forAccount($member)->create();

        CtsBooking::factory()->create([
            'position' => 'EGXX_FSS',
            'member_id' => $ctsMember->id,
            'type' => 'BK',
            'date' => Carbon::tomorrow()->toDateString(),
            'from' => '10:00:00',
            'to' => '12:00:00',
        ]);

        $bookings = app(BookingRepository::class)->getMemberUpcomingBookings($member);

        $this->assertCount(1, $bookings);
        $this->assertSame('cts', $bookings->first()->source);
        $this->assertSame('EGXX_FSS', $bookings->first()->position);
    }

    #[Test]
    public function it_excludes_past_core_bookings(): void
    {
        $member = Account::factory()->create();
        $position = Position::factory()->create(['callsign' => 'EGKK_APP']);

        Booking::create([
            'position_id' => $position->id,
            'member_id' => $member->id,
            'type' => Booking::TYPE_STANDARD,
            'starts_at' => Carbon::yesterday()->setHour(10),
            'ends_at' => Carbon::yesterday()->setHour(12),
        ]);

        $bookings = app(BookingRepository::class)->getMemberUpcomingBookings($member);

        $this->assertTrue($bookings->isEmpty());
    }

    #[Test]
    public function it_excludes_event_bookings(): void
    {
        $member = Account::factory()->create();
        $position = Position::factory()->create(['callsign' => 'EGKK_APP']);

        Booking::create([
            'position_id' => $position->id,
            'member_id' => $member->id,
            'type' => Booking::TYPE_EVENT,
            'starts_at' => Carbon::tomorrow()->setHour(10),
            'ends_at' => Carbon::tomorrow()->setHour(12),
        ]);

        $bookings = app(BookingRepository::class)->getMemberUpcomingBookings($member);

        $this->assertTrue($bookings->isEmpty());
    }

    #[Test]
    public function it_excludes_exam_bookings(): void
    {
        $member = Account::factory()->create();
        $position = Position::factory()->create(['callsign' => 'EGKK_APP']);

        Booking::create([
            'position_id' => $position->id,
            'member_id' => $member->id,
            'type' => Booking::TYPE_EXAM,
            'starts_at' => Carbon::tomorrow()->setHour(10),
            'ends_at' => Carbon::tomorrow()->setHour(12),
        ]);

        $bookings = app(BookingRepository::class)->getMemberUpcomingBookings($member);

        $this->assertTrue($bookings->isEmpty());
    }

    #[Test]
    public function it_excludes_mentoring_bookings(): void
    {
        $member = Account::factory()->create();
        $position = Position::factory()->create(['callsign' => 'EGKK_APP']);

        Booking::create([
            'position_id' => $position->id,
            'member_id' => $member->id,
            'type' => Booking::TYPE_MENTORING,
            'starts_at' => Carbon::tomorrow()->setHour(10),
            'ends_at' => Carbon::tomorrow()->setHour(12),
        ]);

        $bookings = app(BookingRepository::class)->getMemberUpcomingBookings($member);

        $this->assertTrue($bookings->isEmpty());
    }

    #[Test]
    public function it_excludes_cts_exam_bookings(): void
    {
        $member = Account::factory()->create();
        $ctsMember = CtsMember::factory()->forAccount($member)->create();

        CtsBooking::factory()->create([
            'position' => 'EGXX_FSS',
            'member_id' => $ctsMember->id,
            'type' => 'EX',
            'date' => Carbon::tomorrow()->toDateString(),
            'from' => '10:00:00',
            'to' => '12:00:00',
        ]);

        $bookings = app(BookingRepository::class)->getMemberUpcomingBookings($member);

        $this->assertTrue($bookings->isEmpty());
    }

    #[Test]
    public function it_excludes_cts_mentoring_bookings(): void
    {
        $member = Account::factory()->create();
        $ctsMember = CtsMember::factory()->forAccount($member)->create();

        CtsBooking::factory()->create([
            'position' => 'EGXX_FSS',
            'member_id' => $ctsMember->id,
            'type' => 'ME',
            'date' => Carbon::tomorrow()->toDateString(),
            'from' => '10:00:00',
            'to' => '12:00:00',
        ]);

        $bookings = app(BookingRepository::class)->getMemberUpcomingBookings($member);

        $this->assertTrue($bookings->isEmpty());
    }

    #[Test]
    public function it_excludes_cts_event_bookings(): void
    {
        $member = Account::factory()->create();
        $ctsMember = CtsMember::factory()->forAccount($member)->create();

        CtsBooking::factory()->create([
            'position' => 'EGXX_FSS',
            'member_id' => $ctsMember->id,
            'type' => 'EV',
            'date' => Carbon::tomorrow()->toDateString(),
            'from' => '10:00:00',
            'to' => '12:00:00',
        ]);

        $bookings = app(BookingRepository::class)->getMemberUpcomingBookings($member);

        $this->assertTrue($bookings->isEmpty());
    }

    #[Test]
    public function it_does_not_list_other_members_bookings(): void
    {
        $member = Account::factory()->create();
        $other = Account::factory()->create();
        $position = Position::factory()->create(['callsign' => 'EGKK_APP']);

        Booking::create([
            'position_id' => $position->id,
            'member_id' => $other->id,
            'type' => Booking::TYPE_STANDARD,
            'starts_at' => Carbon::tomorrow()->setHour(10),
            'ends_at' => Carbon::tomorrow()->setHour(12),
        ]);

        $bookings = app(BookingRepository::class)->getMemberUpcomingBookings($member);

        $this->assertTrue($bookings->isEmpty());
    }

    #[Test]
    public function it_renders_the_future_bookings_table_for_a_member(): void
    {
        $member = Account::factory()->create();
        $position = Position::factory()->create(['callsign' => 'EGKK_APP']);

        Booking::create([
            'position_id' => $position->id,
            'member_id' => $member->id,
            'type' => Booking::TYPE_STANDARD,
            'starts_at' => Carbon::tomorrow()->setHour(10),
            'ends_at' => Carbon::tomorrow()->setHour(12),
        ]);

        Livewire::actingAs($member)
            ->test(Calendar::class)
            ->assertSee('My future bookings')
            ->assertSee('EGKK_APP');
    }

    #[Test]
    public function it_hides_the_future_bookings_table_from_guests(): void
    {
        Livewire::test(Calendar::class)
            ->assertDontSee('My future bookings');
    }

    #[Test]
    public function it_shows_an_empty_state_when_there_are_no_future_bookings(): void
    {
        $member = Account::factory()->create();

        Livewire::actingAs($member)
            ->test(Calendar::class)
            ->assertSee('No upcoming bookings');
    }

    #[Test]
    public function jump_to_date_moves_the_calendar_to_that_date(): void
    {
        Livewire::actingAs(Account::factory()->create())
            ->test(Calendar::class)
            ->call('jumpToDate', '2026-08-20')
            ->assertSet('selectedDate', Carbon::create(2026, 8, 20));
    }
}
