<?php

declare(strict_types=1);

namespace Tests\Feature\Bookings;

use App\Livewire\Bookings\Calendar;
use App\Models\Atc\Position;
use App\Models\Booking;
use App\Models\Cts\Booking as CtsBooking;
use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use App\Repositories\Cts\BookingRepository;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class CtsBookingsCalendarTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_shows_cts_only_bookings_live(): void
    {
        $date = Carbon::parse('2026-08-01');
        $member = Account::factory()->create();
        Position::factory()->create(['callsign' => 'EGKK_APP']);

        $cts = CtsBooking::factory()->create([
            'position' => 'EGKK_APP',
            'member_id' => $member->id,
            'type' => 'BK',
            'date' => $date->toDateString(),
            'from' => '10:00:00',
            'to' => '12:00:00',
        ]);

        $bookings = app(BookingRepository::class)->getBookings($date);

        $match = $bookings->firstWhere('cts_booking_id', (int) $cts->id);
        $this->assertNotNull($match, 'CTS-only booking should appear');
        $this->assertEquals('cts', $match->source);
        $this->assertEquals('10:00', $match->from);
        $this->assertEquals('12:00', $match->to);
    }

    #[Test]
    public function it_does_not_duplicate_imported_cts_bookings(): void
    {
        $date = Carbon::parse('2026-08-01');
        $member = Account::factory()->create();
        $position = Position::factory()->create(['callsign' => 'EGKK_APP']);

        $cts = CtsBooking::factory()->create([
            'position' => 'EGKK_APP',
            'member_id' => $member->id,
            'type' => 'BK',
            'date' => $date->toDateString(),
            'from' => '10:00:00',
            'to' => '12:00:00',
        ]);

        // Simulate an already-imported mirror row in core.
        Booking::create([
            'position_id' => $position->id,
            'member_id' => $member->id,
            'type' => Booking::TYPE_STANDARD,
            'starts_at' => $date->toDateString().' 10:00:00',
            'ends_at' => $date->toDateString().' 12:00:00',
            'cts_booking_id' => $cts->id,
            'bookable_type' => CtsBooking::class,
            'bookable_id' => $cts->id,
        ]);

        $bookings = app(BookingRepository::class)->getBookings($date);

        $matches = $bookings->where('cts_booking_id', (int) $cts->id);
        $this->assertCount(1, $matches, 'Imported CTS booking must appear exactly once');
        $this->assertEquals('core', $matches->first()->source);
    }

    #[Test]
    public function owner_can_cancel_a_cts_only_standard_booking(): void
    {
        $member = Account::factory()->create();
        $cts = CtsBooking::factory()->create([
            'member_id' => $member->id,
            'type' => 'BK',
            'date' => Carbon::tomorrow()->toDateString(),
            'from' => '10:00:00',
            'to' => '12:00:00',
        ]);

        Livewire::actingAs($member)
            ->test(Calendar::class)
            ->call('cancelBooking', ['cts_booking_id' => (int) $cts->id])
            ->assertDispatched('booking-deleted');

        $this->assertDatabaseMissing('bookings', ['id' => $cts->id], 'cts');
    }

    #[Test]
    public function cancelling_a_mirrored_cts_booking_removes_both_rows(): void
    {
        $member = Account::factory()->create();
        $position = Position::factory()->create(['callsign' => 'EGKK_APP']);
        $cts = CtsBooking::factory()->create([
            'position' => 'EGKK_APP',
            'member_id' => $member->id,
            'type' => 'BK',
            'date' => Carbon::tomorrow()->toDateString(),
            'from' => '10:00:00',
            'to' => '12:00:00',
        ]);
        $core = Booking::create([
            'position_id' => $position->id,
            'member_id' => $member->id,
            'type' => Booking::TYPE_STANDARD,
            'starts_at' => Carbon::tomorrow()->setHour(10),
            'ends_at' => Carbon::tomorrow()->setHour(12),
            'cts_booking_id' => $cts->id,
            'bookable_type' => CtsBooking::class,
            'bookable_id' => $cts->id,
        ]);

        Livewire::actingAs($member)
            ->test(Calendar::class)
            ->call('cancelBooking', ['id' => (string) $core->id])
            ->assertDispatched('booking-deleted');

        $this->assertDatabaseMissing('bookings', ['id' => $core->id]);
        $this->assertDatabaseMissing('bookings', ['id' => $cts->id], 'cts');
    }

    #[Test]
    public function cancelling_a_mirrored_booking_by_cts_id_also_removes_the_core_mirror(): void
    {
        $member = Account::factory()->create();
        $position = Position::factory()->create(['callsign' => 'EGKK_APP']);
        $cts = CtsBooking::factory()->create([
            'position' => 'EGKK_APP',
            'member_id' => $member->id,
            'type' => 'BK',
            'date' => Carbon::tomorrow()->toDateString(),
            'from' => '10:00:00',
            'to' => '12:00:00',
        ]);
        $core = Booking::create([
            'position_id' => $position->id,
            'member_id' => $member->id,
            'type' => Booking::TYPE_STANDARD,
            'starts_at' => Carbon::tomorrow()->setHour(10),
            'ends_at' => Carbon::tomorrow()->setHour(12),
            'cts_booking_id' => $cts->id,
            'bookable_type' => CtsBooking::class,
            'bookable_id' => $cts->id,
        ]);

        Livewire::actingAs($member)
            ->test(Calendar::class)
            ->call('cancelBooking', ['cts_booking_id' => (int) $cts->id])
            ->assertDispatched('booking-deleted');

        $this->assertDatabaseMissing('bookings', ['id' => $core->id]);
        $this->assertDatabaseMissing('bookings', ['id' => $cts->id], 'cts');
    }

    #[Test]
    public function it_rejects_cancelling_someone_elses_cts_booking(): void
    {
        $owner = Account::factory()->create();
        $other = Account::factory()->create();
        $cts = CtsBooking::factory()->create([
            'member_id' => $owner->id,
            'type' => 'BK',
            'date' => Carbon::tomorrow()->toDateString(),
            'from' => '10:00:00',
            'to' => '12:00:00',
        ]);

        Livewire::actingAs($other)
            ->test(Calendar::class)
            ->call('cancelBooking', ['cts_booking_id' => (int) $cts->id])
            ->assertDispatched('booking-error');

        $this->assertDatabaseHas('bookings', ['id' => $cts->id], 'cts');
    }

    #[Test]
    public function it_rejects_cancelling_a_non_standard_cts_booking(): void
    {
        $member = Account::factory()->create();
        $cts = CtsBooking::factory()->create([
            'member_id' => $member->id,
            'type' => 'EX',
            'date' => Carbon::tomorrow()->toDateString(),
            'from' => '10:00:00',
            'to' => '12:00:00',
        ]);

        Livewire::actingAs($member)
            ->test(Calendar::class)
            ->call('cancelBooking', ['cts_booking_id' => (int) $cts->id])
            ->assertDispatched('booking-error');

        $this->assertDatabaseHas('bookings', ['id' => $cts->id], 'cts');
    }

    #[Test]
    public function creating_a_booking_that_overlaps_a_live_cts_booking_is_rejected(): void
    {
        $member = Account::factory()->withQualification()->create();
        $qual = Qualification::factory()->atc()->create(['vatsim' => 5]);
        $member->qualifications()->sync([$qual->id]);
        $member = $member->fresh();

        $position = Position::factory()->create(['type' => Position::TYPE_ENROUTE, 'callsign' => 'LON_CTR']);

        // CTS-only booking (not imported into core) on the same position/time.
        CtsBooking::factory()->create([
            'position' => 'LON_CTR',
            'member_id' => Account::factory()->create()->id,
            'type' => 'BK',
            'date' => Carbon::tomorrow()->toDateString(),
            'from' => '10:00:00',
            'to' => '12:00:00',
        ]);

        Livewire::actingAs($member)
            ->test(Calendar::class)
            ->call('createBooking', [
                'starts_at' => Carbon::tomorrow()->setHour(11)->format('Y-m-d H:i:s'),
                'ends_at' => Carbon::tomorrow()->setHour(13)->format('Y-m-d H:i:s'),
                'position_id' => (string) $position->id,
            ])
            ->assertDispatched('booking-warning');

        $this->assertDatabaseMissing('bookings', ['member_id' => $member->id]);
    }
}
