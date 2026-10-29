<?php

declare(strict_types=1);

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const WINDOW_DAYS = 31;

    public function up(): void
    {
        if (! Schema::connection('cts')->hasTable('availability') || ! Schema::connection('cts')->hasTable('members')) {
            return;
        }

        $today = now()->utc()->startOfDay();
        $lastDay = $today->copy()->addDays(self::WINDOW_DAYS);

        DB::connection('cts')
            ->table('availability as a')
            ->join('members as m', 'm.id', '=', 'a.student_id')
            ->where('a.type', 'S')
            // A day early, so a slot that opened yesterday and is still open is not dropped.
            ->whereBetween('a.date', [$today->copy()->subDay()->toDateString(), $lastDay->toDateString()])
            ->select(['a.date', 'a.from', 'a.to', 'm.cid'])
            ->orderBy('a.id')
            ->chunk(500, function ($rows): void {
                $slots = [];

                foreach ($rows as $row) {
                    $slot = $this->toSlot($row);

                    if ($slot !== null) {
                        $slots[$slot['account_id'].'|'.$slot['starts_at'].'|'.$slot['ends_at']] = $slot;
                    }
                }

                $existingAccounts = DB::table('mship_account')
                    ->whereIn('id', array_values(array_unique(array_column($slots, 'account_id'))))
                    ->pluck('id')
                    ->all();

                $slots = array_filter(
                    $slots,
                    fn (array $slot) => in_array($slot['account_id'], $existingAccounts, true),
                );

                if ($slots !== []) {
                    DB::table('training_availability')->upsert(
                        array_values($slots),
                        ['account_id', 'starts_at', 'ends_at'],
                        ['updated_at'],
                    );
                }
            });
    }

    public function down(): void
    {
        // Backfilled slots may have been edited since, so they are not removed.
    }

    /**
     * @return array<string, mixed>|null
     */
    private function toSlot(object $row): ?array
    {
        $date = (string) $row->date;
        $from = $this->normaliseTime((string) $row->from);
        $to = $this->normaliseTime((string) $row->to);

        $start = Carbon::parse($date.' '.$from, 'UTC');
        $end = Carbon::parse($date.' '.$to, 'UTC');

        if ($to < $from) {
            $end->addDay();
        }

        if ($end->lessThanOrEqualTo($start) || $end->lessThanOrEqualTo(now())) {
            return null;
        }

        return [
            'account_id' => (int) $row->cid,
            'starts_at' => $start->format('Y-m-d H:i:s'),
            'ends_at' => $end->format('Y-m-d H:i:s'),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }

    /**
     * The legacy accessor read a 24:00 end as the end of the day, 23:45.
     */
    private function normaliseTime(string $time): string
    {
        return str_starts_with($time, '24:') ? '23:45:00' : $time;
    }
};
