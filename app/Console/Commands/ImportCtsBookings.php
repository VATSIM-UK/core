<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Atc\Position;
use App\Models\Booking;
use App\Models\Cts\Booking as CtsBooking;
use App\Models\Mship\Account;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ImportCtsBookings extends Command
{
    protected $signature = 'cts:import-bookings
        {--since=2026-01-01 : Only import bookings on or after this date}
        {--dry-run : Preview without writing to the database}';

    protected $description = 'Import CTS bookings into the core bookings table. Re-runnable — skips already-imported records.';

    private const TYPE_MAP = [
        'BK' => Booking::TYPE_STANDARD,
        'EX' => Booking::TYPE_EXAM,
        'ME' => Booking::TYPE_MENTORING,
        'EV' => Booking::TYPE_EVENT,
    ];

    public function handle(): int
    {
        $since = $this->option('since');
        $dryRun = (bool) $this->option('dry-run');

        if ($dryRun) {
            $this->warn('DRY RUN - no changes will be written.');
        }

        $existingIds = Booking::whereNotNull('cts_booking_id')
            ->pluck('cts_booking_id')
            ->toArray();

        $imported = 0;
        $skipped = 0;

        CtsBooking::query()
            ->where('date', '>=', $since)
            ->orderBy('id')
            ->chunk(500, function ($ctsBookings) use ($dryRun, $existingIds, &$imported, &$skipped) {
                $inserts = [];

                foreach ($ctsBookings as $cts) {
                    if (in_array($cts->id, $existingIds)) {
                        $skipped++;

                        continue;
                    }

                    $position = Position::where('callsign', $cts->position)->first();
                    $member = Account::find($cts->member_id);
                    $type = self::TYPE_MAP[$cts->type] ?? Booking::TYPE_STANDARD;

                    $inserts[] = [
                        'position_id' => $position?->id,
                        'member_id' => $member?->id,
                        'type' => $type,
                        'starts_at' => Carbon::parse($cts->date.' '.$cts->from)->format('Y-m-d H:i:s'),
                        'ends_at' => Carbon::parse($cts->date.' '.$cts->to)->format('Y-m-d H:i:s'),
                        'cts_booking_id' => $cts->id,
                        'bookable_type' => null,
                        'bookable_id' => null,
                        'created_at' => $cts->time_booked ?? Carbon::now(),
                        'updated_at' => Carbon::now(),
                    ];

                    $imported++;
                }

                if (! empty($inserts) && ! $dryRun) {
                    DB::table('bookings')->insert($inserts);
                }
            });

        $this->info("Imported: {$imported}, Skipped (already exist): {$skipped}");

        if ($dryRun) {
            $this->warn('DRY RUN — no changes were written. Run without --dry-run to apply.');
        }

        return 0;
    }
}
