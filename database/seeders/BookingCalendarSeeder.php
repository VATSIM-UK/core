<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Atc\Position;
use App\Models\Booking;
use App\Models\Events\Event;
use App\Models\Mship\Account;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use InvalidArgumentException;

class BookingCalendarSeeder extends Seeder
{
    private const CALLSIGNS = [
        'EGLL_DEL', 'EGLL_GND', 'EGLL_TWR', 'EGLL_APP', 'EGLL_ATIS',
        'EGKK_APP', 'EGKK_GND', 'EGCC_GND', 'EGPH_DEL',
        'OBS_PT1',
    ];

    private const DAILY_SLOTS = [
        ['EGLL_APP', 9, 0, 120, Booking::TYPE_STANDARD],
        ['EGLL_TWR', 9, 0, 120, Booking::TYPE_STANDARD],
        ['EGLL_GND', 9, 30, 90, Booking::TYPE_STANDARD],
        ['EGLL_DEL', 9, 0, 90, Booking::TYPE_STANDARD],
        ['EGLL_ATIS', 9, 0, 120, Booking::TYPE_STANDARD],

        ['EGLL_TWR', 12, 0, 90, Booking::TYPE_STANDARD],
        ['EGLL_TWR', 12, 30, 90, Booking::TYPE_MENTORING],

        ['EGLL_TWR', 18, 0, 90, Booking::TYPE_STANDARD],

        ['EGKK_APP', 6, 0, 120, Booking::TYPE_STANDARD],
        ['EGKK_GND', 6, 30, 90, Booking::TYPE_STANDARD],

        ['EGKK_APP', 13, 0, 90, Booking::TYPE_EXAM],

        ['OBS_PT1', 19, 0, 120, Booking::TYPE_GROUP_SEMINAR],

        ['EGCC_GND', 16, 0, 60, Booking::TYPE_STANDARD],
        ['EGPH_DEL', 15, 30, 90, Booking::TYPE_STANDARD],
    ];

    private const EVENTS = [
        ['Seeded: EGCB Real Ops', 0, 9, 0, 3],
        ['Seeded: Controller Meet', 3, 19, 0, 2],
    ];

    public function run(int $cid): void
    {
        if ($cid <= 0) {
            throw new InvalidArgumentException('BookingCalendarSeeder requires a positive owner CID.');
        }

        $member = Account::query()->findOrFail($cid);

        if ($this->command?->confirm("Delete existing seeded bookings/events for CID {$cid} before seeding?", true) ?? true) {
            Booking::query()->where('member_id', $cid)->delete();
            Event::query()->where('name', 'like', 'Seeded:%')->where('published_by', $cid)->delete();
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

            foreach (self::DAILY_SLOTS as [$callsign, $hour, $minute, $durationMinutes, $type]) {
                $startsAt = $day->copy()->setTime($hour, $minute);

                Booking::create([
                    'position_id' => $positions[$callsign]->id,
                    'member_id' => $member->id,
                    'type' => $type,
                    'starts_at' => $startsAt,
                    'ends_at' => $startsAt->copy()->addMinutes($durationMinutes),
                ]);

                $count++;
            }
        }

        foreach (self::EVENTS as [$name, $dayOffset, $hour, $minute, $durationHours]) {
            $start = Carbon::today()->addDays($dayOffset)->setTime($hour, $minute);

            Event::factory()->published($member)->create([
                'name' => $name,
                'start' => $start,
                'end' => $start->copy()->addHours($durationHours),
            ]);
        }

        $this->command?->info(sprintf('Seeded %d bookings across 7 days and %d events for CID %d.', $count, count(self::EVENTS), $cid));
    }
}
