<?php

declare(strict_types=1);

namespace App\Services\Bookings;

use App\Models\Booking;
use App\Models\Cts\Booking as CtsBooking;
use App\Models\Cts\Member as CtsMember;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class BookingPolicy
{
    public function validateAdvanceBookingLimits(int $memberId, Carbon $startsAt, ?int $excludeBookingId = null): void
    {
        $maxDays = (int) config('bookings.max_advance_days', 90);
        $maxBookings = (int) config('bookings.max_advance_bookings', 6);
        $minAdvanceHours = (int) config('bookings.min_advance_hours', 2);

        if ($startsAt->gt(Carbon::now()->addDays($maxDays))) {
            throw new \RuntimeException("You cannot book more than {$maxDays} days in advance.");
        }

        $cutoff = Carbon::now()->addHours($minAdvanceHours);

        $coreCount = Booking::query()
            ->where('member_id', $memberId)
            ->where('type', Booking::TYPE_STANDARD)
            ->where('starts_at', '>', $cutoff)
            ->when($excludeBookingId !== null, fn (Builder $q) => $q->where('id', '!=', $excludeBookingId))
            ->count();

        $importedCtsIds = Booking::query()
            ->where('member_id', $memberId)
            ->whereNotNull('cts_booking_id')
            ->pluck('cts_booking_id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $ctsMember = CtsMember::where('cid', $memberId)->first();
        $ctsCount = 0;
        if ($ctsMember) {
            $ctsCount = CtsBooking::query()
                ->where('member_id', $ctsMember->id)
                ->where('type', 'BK')
                ->when(! empty($importedCtsIds), fn (Builder $q) => $q->whereNotIn('id', $importedCtsIds))
                ->get()
                ->filter(fn (CtsBooking $b) => $this->ctsStartsAfterCutoff($b, $cutoff))
                ->count();
        }

        if ($coreCount + $ctsCount >= $maxBookings) {
            throw new \RuntimeException("You can have a maximum of {$maxBookings} advance bookings.");
        }
    }

    public function validateGatwickLimit(int $memberId, ?int $excludeBookingId = null): void
    {
        $maxGatwick = (int) config('bookings.gatwick.max', 2);
        $patterns = (array) config('bookings.gatwick.position_patterns', ['EGKK_%GND%', 'EGKK_%DEL%']);
        $cutoff = Carbon::now()->addHours((int) config('bookings.min_advance_hours', 2));

        $coreCount = Booking::query()
            ->where('member_id', $memberId)
            ->where('type', Booking::TYPE_STANDARD)
            ->where('starts_at', '>', $cutoff)
            ->when($excludeBookingId !== null, fn (Builder $q) => $q->where('id', '!=', $excludeBookingId))
            ->whereHas('position', function (Builder $q) use ($patterns) {
                $q->where(function (Builder $q) use ($patterns) {
                    foreach ($patterns as $pattern) {
                        $q->orWhere('callsign', 'like', $pattern);
                    }
                });
            })
            ->count();

        $importedCtsIds = Booking::query()
            ->where('member_id', $memberId)
            ->whereNotNull('cts_booking_id')
            ->pluck('cts_booking_id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $ctsMember = CtsMember::where('cid', $memberId)->first();
        $ctsCount = 0;
        if ($ctsMember) {
            $ctsCount = CtsBooking::query()
                ->where('member_id', $ctsMember->id)
                ->where('type', 'BK')
                ->when(! empty($importedCtsIds), fn (Builder $q) => $q->whereNotIn('id', $importedCtsIds))
                ->get()
                ->filter(function (CtsBooking $b) use ($patterns, $cutoff) {
                    return $this->matchesGatwickPattern($b->position, $patterns)
                        && $this->ctsStartsAfterCutoff($b, $cutoff);
                })
                ->count();
        }

        if ($coreCount + $ctsCount >= $maxGatwick) {
            throw new \RuntimeException("You can have a maximum of {$maxGatwick} Gatwick Ground or Delivery bookings.");
        }
    }

    private function matchesGatwickPattern(string $callsign, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            $regex = '/^'.str_replace('%', '.*', preg_quote($pattern, '/')).'$/i';
            if (preg_match($regex, $callsign)) {
                return true;
            }
        }

        return false;
    }

    private function ctsStartsAfterCutoff(CtsBooking $booking, Carbon $cutoff): bool
    {
        $date = Carbon::parse($booking->date);
        $from = substr((string) $booking->from, 0, 5);

        $start = Carbon::parse($date->toDateString().' '.$from);

        // Advance counting is start-based, mirroring the core query's
        // `starts_at > cutoff`. An overnight booking (e.g. 23:00 → 01:00) still
        // starts on its calendar date, so no midnight wrap is needed here.
        return $start->gt($cutoff);
    }
}
