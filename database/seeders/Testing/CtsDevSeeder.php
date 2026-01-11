<?php

namespace Database\Seeders\Testing;

use App\Models\Cts\ExamBooking;
use App\Models\Cts\Member;
use App\Models\Mship\Account;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class CtsDevSeeder extends Seeder
{
    public function run(): void
    {
        // ---- OPTIONAL: wipe old dev data so reruns always show results ----
        // Comment these out if you don't want destructive seeds.
        DB::connection('cts')->table('availability')->truncate();
        DB::connection('cts')->table('exam_book')->truncate();

        // ---- Availability.type enum values (no guessing) ----
        $types = $this->availabilityTypes();

        // ---- Create a coherent dataset: examiners + students ----
        $examiners = Member::factory()
            ->count(8)
            ->state(fn () => ['cid' => random_int(800_000, 1_800_000)])
            ->create();

        $students = Member::factory()
            ->count(60)
            ->state(fn () => ['cid' => random_int(800_000, 1_800_000)])
            ->create();

        $allMembers = $examiners->concat($students);

        // ---- Ensure mship accounts exist for EVERY seeded CTS member (id == cid) ----
        $this->ensureAccounts($allMembers);

        // ---- Seed availability for EVERY seeded member ----
        $availabilityRows = $this->seedAvailability($allMembers, $types);

        // ---- Seed exam bookings using seeded students & examiners ----
        $bookingStats = $this->seedBookings($students, $examiners);

        $this->command?->info(
            "CTS dev seeded: {$allMembers->count()} members, {$availabilityRows} availability rows, ".
            "{$bookingStats['created']} bookings ({$bookingStats['finished']} finished)."
        );
    }

    private function availabilityTypes(): array
    {
        $typeCol = DB::connection('cts')->select("SHOW COLUMNS FROM availability LIKE 'type'");
        $typeEnum = $typeCol[0]->Type ?? null;
        $types = $this->parseEnumValues($typeEnum);

        // If parsing fails, fall back.
        return $types ?: ['STUDENT'];
    }

    private function ensureAccounts($members): void
    {
        foreach ($members as $member) {
            if (empty($member->cid)) {
                continue;
            }

            $cid = (int) $member->cid;

            if (Account::query()->whereKey($cid)->exists()) {
                continue;
            }

            // Try factory first (best chance of satisfying required columns / hooks)
            try {
                Account::factory()->create([
                    'id' => $cid,
                    'name_first' => 'Test',
                    'name_last'  => "CTS{$cid}",
                    'email' => "cts{$cid}@example.test",
                ]);
                continue;
            } catch (\Throwable $e) {
                // Fall back to a minimal valid create for your schema
            }

            Account::query()->create([
                'id' => $cid,
                'name_first' => 'Test',
                'name_last'  => "CTS{$cid}",
                'email' => "cts{$cid}@example.test",
                'joined_at' => now(),
            ]);
        }
    }

    private function seedAvailability($members, array $types): int
    {
        $durations = [60, 75, 90, 105, 120, 135, 150];
        $minuteChoices = [0, 15, 30, 45];

        $rows = 0;

        foreach ($members as $member) {
            $slotCount = random_int(6, 12);

            // Track uniqueness per member: date|from|to
            $used = [];

            $attempts = 0;
            while (count($used) < $slotCount && $attempts < 200) {
                $attempts++;

                $date = Carbon::now()->addDays(random_int(1, 30))->toDateString();

                $from = Carbon::createFromTime(
                    random_int(8, 21),
                    $minuteChoices[array_rand($minuteChoices)],
                    0
                );

                $durationMins = $durations[array_rand($durations)];
                $to = (clone $from)->addMinutes($durationMins);

                $fromStr = $from->format('H:i:s');
                $toStr   = $to->format('H:i:s');

                $key = "{$date}|{$fromStr}|{$toStr}";
                if (isset($used[$key])) {
                    continue; // try again
                }
                $used[$key] = true;

                $type = $types[array_rand($types)];

                // Use insertOrIgnore to handle any DB-level uniqueness we didn't model (e.g. includes type)
                $inserted = DB::connection('cts')->table('availability')->insertOrIgnore([
                    'student_id' => $member->id,
                    'type'       => $type,
                    'date'       => $date,
                    'from'       => $fromStr,
                    'to'         => $toStr,
                ]);

                // insertOrIgnore returns number of inserted rows for some drivers; treat truthy as inserted
                if ($inserted) {
                    $rows++;
                }
            }
        }

        return $rows;
    }


    private function seedBookings($students, $examiners): array
    {
        $positions = collect(['EGLL_TWR','EGKK_TWR','EGCC_TWR','EGPH_TWR','EGSS_TWR','EGGW_TWR']);
        $exam = 'TWR'; // valid enum value

        $created = 0;
        $finished = 0;

        foreach ($students as $student) {
            $bookingCount = random_int(1, 4);

            for ($i = 0; $i < $bookingCount; $i++) {
                $examiner = $examiners->random();

                $bookedAt = Carbon::now()
                    ->subDays(random_int(1, 60))
                    ->subMinutes(random_int(0, 1440));

                [$date1, $from1, $to1] = $this->makeSlot($bookedAt, random_int(1, 21));

                $has2 = random_int(1, 100) <= 60;
                $has3 = random_int(1, 100) <= 40;

                [$date2, $from2, $to2] = $has2 ? $this->makeSlot($bookedAt, random_int(3, 28)) : [null, null, null];
                [$date3, $from3, $to3] = $has3 ? $this->makeSlot($bookedAt, random_int(7, 35)) : [null, null, null];

                $isTaken    = random_int(1, 100) <= 70;
                $isFinished = $isTaken && (random_int(1, 100) <= 95);
                $passed     = $isFinished ? (random_int(1, 100) <= 65) : 0;

                $takenDate = $isTaken ? $date1 : null;
                $takenFrom = $isTaken ? $from1 : null;
                $takenTo   = $isTaken ? $to1   : null;

                $timeTaken = $isTaken && $takenDate && $takenFrom
                    ? Carbon::createFromFormat('Y-m-d H:i:s', "{$takenDate} {$takenFrom}")
                    : null;

                ExamBooking::query()->create([
                    'rts_id' => 0,

                    'student_id'     => $student->id,
                    'student_rating' => 1,

                    'exam' => $exam,

                    'position_1' => $positions->random(),
                    'position_2' => (random_int(1, 100) <= 35) ? $positions->random() : null,

                    'date_1' => $date1, 'from_1' => $from1, 'to_1' => $to1,
                    'date_2' => $date2, 'from_2' => $from2, 'to_2' => $to2,
                    'date_3' => $date3, 'from_3' => $from3, 'to_3' => $to3,

                    'exmr_id'     => $examiner->id,
                    'exmr_rating' => 3,

                    'time_book'  => $bookedAt,
                    'time_taken' => $timeTaken,

                    'taken'      => $isTaken ? 1 : 0,
                    'taken_date' => $takenDate,
                    'taken_from' => $takenFrom,
                    'taken_to'   => $takenTo,

                    'book_done' => $isFinished ? 1 : 0,
                    'finished'  => $isFinished ? 1 : 0,
                    'pass'      => $passed,

                    'second_examiner_req' => (random_int(1, 100) <= 10) ? 1 : 0,
                ]);

                $created++;
                if ($isFinished) $finished++;
            }
        }

        return ['created' => $created, 'finished' => $finished];
    }

    private function makeSlot(Carbon $base, int $daysAhead): array
    {
        $date = (clone $base)->addDays($daysAhead)->toDateString();

        $minuteChoices = [0, 15, 30, 45];
        $from = Carbon::createFromTime(
            random_int(8, 21),
            $minuteChoices[array_rand($minuteChoices)],
            0
        )->format('H:i:s');

        $to = Carbon::createFromFormat('H:i:s', $from)
            ->addMinutes(90)
            ->format('H:i:s');

        return [$date, $from, $to];
    }

    private function parseEnumValues(?string $mysqlType): array
    {
        if (!$mysqlType || !str_starts_with($mysqlType, "enum(")) {
            return [];
        }

        $inside = substr($mysqlType, 5, -1);
        $parts = array_map('trim', explode(',', $inside));

        $values = [];
        foreach ($parts as $p) {
            $p = preg_replace("/^'(.*)'$/", "$1", trim($p));
            $values[] = stripslashes($p);
        }

        return array_values(array_filter($values, fn ($v) => $v !== ''));
    }
}
