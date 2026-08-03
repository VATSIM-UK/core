<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const EXAM_BOOKING_TYPE = 'App\\Models\\Cts\\ExamBooking';

    private const SESSION_TYPE = 'App\\Models\\Cts\\Session';

    /**
     * Create CTS booking rows for training-panel exam/mentoring bookings that were
     * written to core only (before acceptances started mirroring into both tables).
     * This backfills the ~1 week of bookings missing from the legacy CTS calendar.
     */
    public function up(): void
    {
        DB::table('bookings')
            ->whereIn('type', ['exam', 'mentoring'])
            ->whereNull('cts_booking_id')
            ->orderBy('id')
            ->chunk(500, function ($coreBookings): void {
                foreach ($coreBookings as $core) {
                    $ctsMemberId = DB::connection('cts')->table('members')
                        ->where('cid', $core->member_id)
                        ->value('id');

                    if (! $ctsMemberId) {
                        continue;
                    }

                    // Reuse an existing CTS row for this booking if one already exists
                    // (the CTS insert runs on a separate connection, so a partial failure
                    // of this migration would otherwise leave rows that get duplicated on
                    // a re-run).
                    $ctsId = DB::connection('cts')->table('bookings')
                        ->where('member_id', $ctsMemberId)
                        ->where('date', substr($core->starts_at, 0, 10))
                        ->where('from', substr($core->starts_at, 11, 8))
                        ->where('type', $core->type === 'exam' ? 'EX' : 'ME')
                        ->where('position', $this->resolveCallsign($core))
                        ->value('id');

                    if (! $ctsId) {
                        $ctsId = DB::connection('cts')->table('bookings')->insertGetId([
                            'date' => substr($core->starts_at, 0, 10),
                            'from' => substr($core->starts_at, 11, 8),
                            'to' => substr($core->ends_at, 11, 8),
                            'position' => $this->resolveCallsign($core),
                            'member_id' => $ctsMemberId,
                            'type' => $core->type === 'exam' ? 'EX' : 'ME',
                            'local_id' => 0,
                            'time_booked' => $core->created_at ?? now(),
                        ]);
                    }

                    // Link the core booking back to the CTS row via the FK.
                    DB::table('bookings')
                        ->where('id', $core->id)
                        ->update(['cts_booking_id' => $ctsId]);
                }
            });
    }

    /**
     * Recover the callsign for a training-panel booking. The core row only stores
     * position_id (null when the callsign had no match in the positions table), so
     * fall back to the linked exam/session record which holds the original callsign.
     */
    private function resolveCallsign(object $core): string
    {
        if ($core->bookable_type === self::EXAM_BOOKING_TYPE) {
            $callsign = DB::connection('cts')->table('exam_book')->where('id', $core->bookable_id)->value('position_1');
        } elseif ($core->bookable_type === self::SESSION_TYPE) {
            $callsign = DB::connection('cts')->table('sessions')->where('id', $core->bookable_id)->value('position');
        }

        if (empty($callsign)) {
            $callsign = DB::table('positions')->where('id', $core->position_id)->value('callsign');
        }

        return (string) ($callsign ?? '');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No-op: this migration is a data backfill.
    }
};
