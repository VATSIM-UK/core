<?php

declare(strict_types=1);

use App\Models\Atc\Position;
use App\Models\Booking;
use App\Models\Cts\Booking as CtsBooking;
use App\Models\Mship\Account;
use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const TYPE_MAP = [
        'BK' => Booking::TYPE_STANDARD,
        'EX' => Booking::TYPE_EXAM,
        'ME' => Booking::TYPE_MENTORING,
        'EV' => Booking::TYPE_EVENT,
    ];

    public function up(): void
    {
        CtsBooking::query()
            ->orderBy('id')
            ->chunk(500, function ($ctsBookings) {
                $inserts = [];

                foreach ($ctsBookings as $cts) {
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
                }

                if (! empty($inserts)) {
                    DB::table('bookings')->insert($inserts);
                }
            });
    }

    public function down(): void
    {
        DB::table('bookings')->truncate();
    }
};
