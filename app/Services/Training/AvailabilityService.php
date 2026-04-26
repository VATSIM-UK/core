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
                $now = now()->utc();
                $query->whereDate('date', '>', $now->toDateString())
                    ->orWhere(function ($q) use ($now) {
                        $q->whereDate('date', $now->toDateString())
                            ->whereTime('to', '>', $now->format('H:i:s'));
                    });
            });
    }

    public function formatDuration(Carbon $date, Carbon $from, Carbon $to): string
    {
        $start = Carbon::parse($date->format('Y-m-d').' '.$from->format('H:i:s'));
        $end = Carbon::parse($date->format('Y-m-d').' '.$to->format('H:i:s'));

        if ($to->format('H:i:s') < $from->format('H:i:s')) {
            $end->addDay();
        }

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

    public function isSlotValid(int $studentId, Carbon $startUtc, Carbon $endUtc, ?int $ignoreId = null): array
    {
        if ($startUtc->greaterThanOrEqualTo($endUtc)) {
            return [false, 'The availability end time must be after the start time.'];
        }

        if ($startUtc->isPast()) {
            return [false, 'Availability cannot be in the past.'];
        }

        $overlapExists = Availability::query()
            ->where('student_id', $studentId)
            ->where('type', 'S')
            ->when($ignoreId, fn (Builder $query) => $query->where('id', '!=', $ignoreId))
            ->whereBetween('date', [$startUtc->clone()->subDay()->toDateString(), $startUtc->clone()->addDay()->toDateString()])
            ->get()
            ->contains(function (Availability $slot) use ($startUtc, $endUtc) {
                $slotStart = Carbon::parse($slot->date->format('Y-m-d').' '.$slot->from->format('H:i:s'), 'UTC');
                $slotEnd = Carbon::parse($slot->date->format('Y-m-d').' '.$slot->to->format('H:i:s'), 'UTC');

                if ($slot->to->format('H:i:s') < $slot->from->format('H:i:s')) {
                    $slotEnd->addDay();
                }

                return $startUtc->lessThan($slotEnd) && $endUtc->greaterThan($slotStart);
            });

        if ($overlapExists) {
            return [false, 'This availability slot overlaps with an existing entry.'];
        }

        return [true, 'Valid'];
    }

    public function addOrMergeSlot(int $studentId, Carbon $startUtc, Carbon $endUtc): string
    {
        $overlapping = Availability::where('student_id', $studentId)
            ->where('type', 'S')
            ->where('date', $startUtc->toDateString())
            ->where('from', '<', $endUtc->format('H:i:s'))
            ->where('to', '>', $startUtc->format('H:i:s'))
            ->first();

        if ($overlapping) {
            $existingStart = Carbon::parse($overlapping->date->format('Y-m-d').' '.$overlapping->from->format('H:i:s'), 'UTC');
            $existingEnd = Carbon::parse($overlapping->date->format('Y-m-d').' '.$overlapping->to->format('H:i:s'), 'UTC');

            $mergedStart = $startUtc->min($existingStart);
            $mergedEnd = $endUtc->max($existingEnd);

            $overlapping->update([
                'date' => $mergedStart->toDateString(),
                'from' => $mergedStart->format('H:i:s'),
                'to' => $mergedEnd->format('H:i:s'),
            ]);

            return 'merged';
        }

        Availability::create([
            'student_id' => $studentId,
            'type' => 'S',
            'date' => $startUtc->toDateString(),
            'from' => $startUtc->format('H:i:s'),
            'to' => $endUtc->format('H:i:s'),
        ]);

        return 'added';
    }
}
