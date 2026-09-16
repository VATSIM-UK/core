<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Atc\Position;
use App\Models\Booking;
use App\Models\Mship\Account;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use InvalidArgumentException;

/**
 * Seeds standard bookings across the next 7 days (today through +6), owned by an
 * existing account, to exercise the bookings calendar (day and week views) locally.
 *
 * Usage: php artisan bookings:seed-calendar --user=<cid>
 */
class BookingCalendarSeeder extends Seeder
{
    private const CALLSIGNS = ['EGKK_APP', 'EGLL_TWR', 'EGCC_GND', 'EGPH_DEL'];

    private const DAILY_SLOTS = [
        ['EGKK_APP', 6, 0, 120],
        ['EGKK_APP', 7, 0, 120], // overlaps the slot above, to exercise merging
        ['EGLL_TWR', 8, 30, 90],
        ['EGCC_GND', 10, 0, 60],
        ['EGPH_DEL', 11, 30, 90],
        ['EGKK_APP', 15, 30, 90],
        ['EGLL_TWR', 18, 0, 120],
        ['EGCC_GND', 20, 30, 90],
    ];

    public function run(int $cid): void
    {
        if ($cid <= 0) {
            throw new InvalidArgumentException('BookingCalendarSeeder requires a positive owner CID.');
        }

        $member = Account::query()->findOrFail($cid);

        if ($this->command?->confirm("Delete existing bookings for CID {$cid} before seeding?", true) ?? true) {
            Booking::query()->where('member_id', $cid)->delete();
        }

        $positions = collect(self::CALLSIGNS)->mapWithKeys(fn (string $callsign) => [
            $callsign => Position::query()->firstOrCreate(
                ['callsign' => $callsign],
                [
                    'name' => $callsign,
                    'frequency' => 118.500,
                    'type' => Position::inferTypeFromCallsign($callsign),
                ],
            ),
        ]);

        $count = 0;

        for ($dayOffset = 0; $dayOffset < 7; $dayOffset++) {
            $day = Carbon::today()->addDays($dayOffset);

            foreach (self::DAILY_SLOTS as [$callsign, $hour, $minute, $durationMinutes]) {
                $startsAt = $day->copy()->setTime($hour, $minute);

                Booking::create([
                    'position_id' => $positions[$callsign]->id,
                    'member_id' => $member->id,
                    'type' => Booking::TYPE_STANDARD,
                    'starts_at' => $startsAt,
                    'ends_at' => $startsAt->copy()->addMinutes($durationMinutes),
                ]);

                $count++;
            }
        }

        $this->command?->info(sprintf('Seeded %d standard bookings across 7 days for CID %d.', $count, $cid));
    }
}
