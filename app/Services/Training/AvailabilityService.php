<?php

namespace App\Services\Training;

use App\Models\Cts\Availability;
use App\Models\Cts\Member;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class AvailabilityService
{
    public function resolveMemberId(int $cid): ?int
    {
        return Member::where('cid', $cid)->value('id');
    }

    public function getFutureAvailabilityQuery(int $memberId): Builder
    {
        return Availability::query()
            ->where('student_id', $memberId)
            ->where('type', 'S')
            ->where(function (Builder $query) {
                $now = now();
                $query->whereDate('date', '>', $now->toDateString())
                    ->orWhere(function ($q) use ($now) {
                        $q->whereDate('date', $now->toDateString())
                            ->whereTime('from', '>', $now->format('H:i:s'));
                    });
            });
    }

    public function formatDuration(Carbon $date, Carbon $from, Carbon $to): string
    {
        $start = Carbon::parse($date->format('Y-m-d').' '.$from->format('H:i:s'));
        $end = Carbon::parse($date->format('Y-m-d').' '.$to->format('H:i:s'));

        $minutes = max(0, $start->diffInMinutes($end));
        $hours = intdiv($minutes, 60);
        $rem = $minutes % 60;

        if ($hours === 0) {
            return "{$rem}m";
        }
        if ($rem === 0) {
            return "{$hours}h";
        }

        return "{$hours}h {$rem}m";
    }

    public function isSlotValid(int $studentId, string $date, string $from, string $to, ?int $ignoreId = null): array
    {
        if ($from >= $to) {
            return [false, 'The availability end time must be after the start time.'];
        }

        $slotStart = Carbon::parse("{$date} {$from}");
        if ($slotStart->isPast()) {
            return [false, 'Availability cannot be in the past.'];
        }

        $overlapExists = Availability::query()
            ->where('student_id', $studentId)
            ->where('date', $date)
            ->where('type', 'S')
            ->when($ignoreId, fn (Builder $query) => $query->where('id', '!=', $ignoreId))
            ->where(function (Builder $query) use ($from, $to) {
                $query->where('from', '<', $to)
                    ->where('to', '>', $from);
            })
            ->exists();

        if ($overlapExists) {
            return [false, 'This availability slot overlaps with an existing entry.'];
        }

        return [true, 'Valid'];
    }
}
