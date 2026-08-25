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
 * Seeds standard bookings for today, owned by an existing account, to exercise the bookings calendar locally.
 *
 * Usage: php artisan bookings:seed-calendar --user=<cid>
 */
class BookingCalendarSeeder extends Seeder
{
    private const CALLSIGNS = ['EGKK_APP', 'EGLL_TWR', 'EGCC_GND', 'EGPH_DEL', 'EGBB_ATIS'];

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

        $today = Carbon::today();

        $schedule = [
            ['EGKK_APP', $today->copy()->setTime(6, 0), 120],
            ['EGLL_TWR', $today->copy()->setTime(8, 30), 90],
            ['EGCC_GND', $today->copy()->setTime(10, 0), 60],
            ['EGPH_DEL', $today->copy()->setTime(11, 30), 90],
            ['EGBB_ATIS', $today->copy()->setTime(13, 0), 120],
            ['EGKK_APP', $today->copy()->setTime(15, 30), 90],
            ['EGLL_TWR', $today->copy()->setTime(18, 0), 120],
            ['EGCC_GND', $today->copy()->setTime(20, 30), 90],
        ];

        foreach ($schedule as [$callsign, $startsAt, $durationMinutes]) {
            Booking::create([
                'position_id' => $positions[$callsign]->id,
                'member_id' => $member->id,
                'type' => Booking::TYPE_STANDARD,
                'starts_at' => $startsAt,
                'ends_at' => $startsAt->copy()->addMinutes($durationMinutes),
            ]);
        }

        $this->command?->info(sprintf('Seeded %d standard bookings for CID %d.', count($schedule), $cid));
    }
}
