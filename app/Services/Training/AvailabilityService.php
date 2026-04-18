<?php

namespace App\Services\Training;

use App\Models\Cts\Availability;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class AvailabilityService
{
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
