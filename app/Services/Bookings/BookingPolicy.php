<?php

declare(strict_types=1);

namespace App\Services\Bookings;

use App\Models\Atc\Position;
use App\Models\Atc\PositionGroup;
use App\Models\Booking;
use App\Models\Cts\Booking as CtsBooking;
use App\Models\Cts\Member as CtsMember;
use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
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

        $ctsCount = $this->countCtsAdvanceBookings($memberId, $cutoff);

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

        $ctsCount = $this->countCtsAdvanceBookings(
            $memberId,
            $cutoff,
            fn (CtsBooking $b) => $this->matchesGatwickPattern($b->position, $patterns)
        );

        if ($coreCount + $ctsCount >= $maxGatwick) {
            throw new \RuntimeException("You can have a maximum of {$maxGatwick} Gatwick Ground or Delivery bookings.");
        }
    }

    public function validateMinimumNotice(int $memberId, int $positionId, Carbon $startsAt): void
    {
        $minAdvanceHours = (int) config('bookings.min_advance_hours', 2);

        if ($startsAt->gte(Carbon::now()->addHours($minAdvanceHours))) {
            return;
        }

        $position = Position::findOrFail($positionId);

        $controllingNow = \App\Models\NetworkData\Atc::query()
            ->where('account_id', $memberId)
            ->whereNull('disconnected_at')
            ->where('callsign', $position->callsign)
            ->where('connected_at', '<=', $startsAt)
            ->exists();

        if (! $controllingNow) {
            throw new \RuntimeException("You must be currently controlling this position to book less than {$minAdvanceHours} hours in advance.");
        }
    }

    public function validateFutureQualification(int $memberId, int $positionId, Carbon $startsAt): void
    {
        $member = Account::with(['endorsements.endorsable', 'states', 'qualifications'])->findOrFail($memberId);
        $position = Position::with('positionGroups.maximumAtcQualification')->findOrFail($positionId);

        if ($position->positionGroups->isEmpty()) {
            return;
        }

        $hadExpiredEndorsementOnly = false;

        $entitled = $position->positionGroups->some(function (PositionGroup $group) use ($member, $startsAt, &$hadExpiredEndorsementOnly): bool {
            if ($this->isEntitledByHomeMemberRating($member, $group)) {
                return true;
            }

            if ($this->isEntitledByVisitorRatingEndorsement($member, $group, $startsAt)) {
                return true;
            }

            $groupEndorsements = $member->endorsements->filter(
                fn ($endorsement) => $endorsement->endorsable_type === PositionGroup::class
                    && (int) $endorsement->endorsable_id === (int) $group->id
            );

            $validAtStart = $groupEndorsements->first(
                fn ($endorsement) => $endorsement->expires_at === null || $endorsement->expires_at->gt($startsAt)
            );

            if ($validAtStart) {
                return true;
            }

            if ($groupEndorsements->isNotEmpty()) {
                $hadExpiredEndorsementOnly = true;
            }

            return false;
        });

        if ($entitled) {
            return;
        }

        if ($hadExpiredEndorsementOnly) {
            throw new \RuntimeException('Your endorsement for this position will have expired by the booked time.');
        }

        throw new \RuntimeException('You do not have a valid endorsement for this position.');
    }

    private function isEntitledByHomeMemberRating(Account $member, PositionGroup $group): bool
    {
        return isset($group->maximumAtcQualification)
            && $member->hasState('DIVISION')
            && $member->qualification_atc?->vatsim > $group->maximumAtcQualification->vatsim;
    }

    private function isEntitledByVisitorRatingEndorsement(Account $member, PositionGroup $group, Carbon $startsAt): bool
    {
        if (! isset($group->maximumAtcQualification)) {
            return false;
        }

        if (! $member->hasState('VISITING') && ! $member->hasState('TRANSFERRING')) {
            return false;
        }

        $highestEndorsedRating = $member->endorsements
            ->filter(
                fn ($endorsement) => $endorsement->endorsable_type === Qualification::class
                    && ($endorsement->expires_at === null || $endorsement->expires_at->gt($startsAt))
            )
            ->sortByDesc(fn ($endorsement) => $endorsement->endorsable?->vatsim)
            ->first()?->endorsable?->vatsim;

        return $highestEndorsedRating !== null
            && $highestEndorsedRating > $group->maximumAtcQualification->vatsim;
    }

    private function countCtsAdvanceBookings(int $memberId, Carbon $cutoff, ?callable $predicate = null): int
    {
        $importedCtsIds = Booking::query()
            ->where('member_id', $memberId)
            ->whereNotNull('cts_booking_id')
            ->pluck('cts_booking_id')
            ->map(fn ($id) => (int) $id)
            ->values()
            ->all();

        $ctsMember = CtsMember::where('cid', $memberId)->first();
        if (! $ctsMember) {
            return 0;
        }

        return CtsBooking::query()
            ->where('member_id', $ctsMember->id)
            ->where('type', 'BK')
            ->when(! empty($importedCtsIds), fn (Builder $q) => $q->whereNotIn('id', $importedCtsIds))
            ->get()
            ->filter(function (CtsBooking $b) use ($cutoff, $predicate) {
                if ($predicate !== null && ! $predicate($b)) {
                    return false;
                }

                return $this->ctsStartsAfterCutoff($b, $cutoff);
            })
            ->count();
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
