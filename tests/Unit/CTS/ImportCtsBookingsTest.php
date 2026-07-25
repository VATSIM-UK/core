<?php

declare(strict_types=1);

namespace Tests\Unit\CTS;

use App\Models\Atc\Position;
use App\Models\Booking;
use App\Models\Cts\Booking as CtsBooking;
use App\Models\Mship\Account;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class ImportCtsBookingsTest extends TestCase
{
    use DatabaseTransactions;

    #[Test]
    public function it_imports_cts_bookings_into_core(): void
    {
        $position = Position::factory()->create(['callsign' => 'EGKK_APP']);
        $member = Account::factory()->create();

        $ctsBooking = CtsBooking::factory()->create([
            'position' => 'EGKK_APP',
            'member_id' => $member->id,
            'type' => 'BK',
            'date' => '2026-08-01',
            'from' => '10:00:00',
            'to' => '12:00:00',
        ]);

        $this->assertDatabaseMissing('bookings', [
            'cts_booking_id' => $ctsBooking->id,
        ]);

        Artisan::call('cts:import-bookings', ['--since' => '2026-01-01']);

        $this->assertDatabaseHas('bookings', [
            'position_id' => $position->id,
            'member_id' => $member->id,
            'type' => Booking::TYPE_STANDARD,
            'cts_booking_id' => $ctsBooking->id,
            'bookable_type' => CtsBooking::class,
            'bookable_id' => $ctsBooking->id,
        ]);
    }

    #[Test]
    public function it_skips_already_imported_bookings(): void
    {
        $position = Position::factory()->create(['callsign' => 'EGKK_APP']);
        $member = Account::factory()->create();

        $ctsBooking = CtsBooking::factory()->create([
            'position' => 'EGKK_APP',
            'member_id' => $member->id,
            'type' => 'BK',
            'date' => '2026-08-01',
            'from' => '10:00:00',
            'to' => '12:00:00',
        ]);

        Artisan::call('cts:import-bookings', ['--since' => '2026-01-01']);

        $countBefore = Booking::count();

        Artisan::call('cts:import-bookings', ['--since' => '2026-01-01']);
        $output = Artisan::output();

        $this->assertStringContainsString('Skipped (already exist): 1', $output);
        $this->assertEquals($countBefore, Booking::count());
    }

    #[Test]
    public function it_respects_since_option(): void
    {
        $position = Position::factory()->create(['callsign' => 'EGCC_APP']);
        $member = Account::factory()->create();

        $oldBooking = CtsBooking::factory()->create([
            'position' => 'EGCC_APP',
            'member_id' => $member->id,
            'type' => 'BK',
            'date' => '2025-12-15',
            'from' => '10:00:00',
            'to' => '12:00:00',
        ]);

        CtsBooking::factory()->create([
            'position' => 'EGCC_APP',
            'member_id' => $member->id,
            'type' => 'BK',
            'date' => '2026-08-01',
            'from' => '14:00:00',
            'to' => '16:00:00',
        ]);

        Artisan::call('cts:import-bookings', ['--since' => '2026-01-01']);

        $this->assertDatabaseMissing('bookings', ['cts_booking_id' => $oldBooking->id]);
    }

    #[Test]
    public function dry_run_does_not_write_to_database(): void
    {
        $position = Position::factory()->create(['callsign' => 'EGGP_GND']);
        $member = Account::factory()->create();

        CtsBooking::factory()->create([
            'position' => 'EGGP_GND',
            'member_id' => $member->id,
            'type' => 'BK',
            'date' => '2026-08-01',
            'from' => '10:00:00',
            'to' => '12:00:00',
        ]);

        Artisan::call('cts:import-bookings', ['--since' => '2026-01-01', '--dry-run' => true]);

        $this->assertEquals(0, Booking::count());
        $this->assertStringContainsString('DRY RUN', Artisan::output());
    }

    #[Test]
    public function it_handles_unknown_position_gracefully(): void
    {
        $member = Account::factory()->create();

        $ctsBooking = CtsBooking::factory()->create([
            'position' => 'EG99_XXX',
            'member_id' => $member->id,
            'type' => 'BK',
            'date' => '2026-08-01',
            'from' => '10:00:00',
            'to' => '12:00:00',
        ]);

        Artisan::call('cts:import-bookings', ['--since' => '2026-01-01']);

        $this->assertDatabaseHas('bookings', [
            'position_id' => null,
            'member_id' => $member->id,
            'cts_booking_id' => $ctsBooking->id,
        ]);
    }

    #[Test]
    public function it_maps_exam_types_correctly(): void
    {
        $member = Account::factory()->create();

        $ctsBooking = CtsBooking::factory()->create([
            'member_id' => $member->id,
            'type' => 'EX',
            'date' => '2026-08-01',
            'from' => '10:00:00',
            'to' => '12:00:00',
        ]);

        Artisan::call('cts:import-bookings', ['--since' => '2026-01-01']);

        $this->assertDatabaseHas('bookings', [
            'member_id' => $member->id,
            'type' => Booking::TYPE_EXAM,
            'cts_booking_id' => $ctsBooking->id,
        ]);
    }
}
