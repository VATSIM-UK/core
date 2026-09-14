<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\Atc\Position;
use App\Models\Booking;
use App\Models\Cts\Booking as CtsBooking;
use App\Models\Cts\Member as CtsMember;
use App\Models\Mship\Account;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class ImportCtsBookings extends Command
{
    protected $signature = 'cts:import-bookings
        {--since=2026-01-01 : Only import bookings on or after this date}
        {--dry-run : Preview without writing to the database}';

    protected $description = 'Import CTS bookings into the core bookings table. Re-runnable — updates existing records by cts_booking_id.';

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

        $existing = Booking::whereNotNull('cts_booking_id')
            ->pluck('id', 'cts_booking_id')
            ->toArray();

        $imported = 0;
        $updated = 0;

        CtsBooking::query()
            ->where('date', '>=', $since)
            ->orderBy('id')
            ->chunk(500, function ($ctsBookings) use ($dryRun, $existing, &$imported, &$updated) {
                $inserts = [];

                foreach ($ctsBookings as $cts) {
                    $position = Position::where('callsign', $cts->position)->first();
                    $member = $this->resolveCoreMember($cts->member_id);
                    $type = self::TYPE_MAP[$cts->type] ?? Booking::TYPE_STANDARD;

                    $startsAt = Carbon::parse($cts->date)->format('Y-m-d').' '.$cts->from;
                    $endsAt = Carbon::parse($cts->date)->format('Y-m-d').' '.$cts->to;

                    $data = [
                        'position_id' => $position?->id,
                        'member_id' => $member?->id,
                        'type' => $type,
                        'starts_at' => $startsAt,
                        'ends_at' => $endsAt,
                        'bookable_type' => CtsBooking::class,
                        'bookable_id' => $cts->id,
                        'updated_at' => Carbon::now(),
                    ];

                    if (isset($existing[$cts->id])) {
                        if (! $dryRun) {
                            DB::table('bookings')
                                ->where('id', $existing[$cts->id])
                                ->update($data);
                        }

                        $updated++;
                    } else {
                        $data['cts_booking_id'] = $cts->id;
                        $data['created_at'] = $cts->time_booked ?? Carbon::now();

                        $inserts[] = $data;
                        $imported++;
                    }
                }

                if (! empty($inserts) && ! $dryRun) {
                    DB::table('bookings')->insert($inserts);
                }
            });

        $this->info("Imported: {$imported}, Updated: {$updated}");

        if ($dryRun) {
            $this->warn('DRY RUN — no changes were written. Run without --dry-run to apply.');
        }

        return 0;
    }

    private function resolveCoreMember(int $ctsMemberId): ?Account
    {
        $ctsMember = CtsMember::find($ctsMemberId);

        if (! $ctsMember) {
            return null;
        }

        return Account::find($ctsMember->cid);
    }
}
