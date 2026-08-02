<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Atc\Position;
use App\Models\Booking;
use App\Models\Cts\Booking as CtsBooking;
use App\Models\Mship\Account;
use App\Services\Bookings\BookingPolicy;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class BookingService
{
    public function __construct(
        private readonly BookingPolicy $policy,
    ) {}

    public function create(array $data): Booking
    {
        if ($data['position_id'] !== null) {
            $this->validateOverlap(
                Carbon::parse($data['starts_at']),
                Carbon::parse($data['ends_at']),
                $data['position_id']
            );
        }

        if (isset($data['member_id'])) {
            $this->validateMemberOverlap(
                Carbon::parse($data['starts_at']),
                Carbon::parse($data['ends_at']),
                $data['member_id']
            );

            if ($data['position_id'] !== null && ($data['type'] ?? Booking::TYPE_STANDARD) === Booking::TYPE_STANDARD) {
                $this->validateMemberQualification(
                    $data['member_id'],
                    $data['position_id']
                );

                $this->policy->validateAdvanceBookingLimits(
                    $data['member_id'],
                    Carbon::parse($data['starts_at'])
                );
                $this->policy->validateGatwickLimit($data['member_id']);
                $this->policy->validateMinimumNotice(
                    $data['member_id'],
                    $data['position_id'],
                    Carbon::parse($data['starts_at'])
                );
                $this->policy->validateFutureQualification(
                    $data['member_id'],
                    $data['position_id'],
                    Carbon::parse($data['starts_at'])
                );
            }
        }

        return Booking::create($data);
    }

    public function update(Booking $booking, array $data): Booking
    {
        $startsAt = Carbon::parse($data['starts_at'] ?? $booking->starts_at);
        $endsAt = Carbon::parse($data['ends_at'] ?? $booking->ends_at);
        $positionId = $data['position_id'] ?? $booking->position_id;
        $memberId = array_key_exists('member_id', $data) ? $data['member_id'] : $booking->member_id;
        $type = array_key_exists('type', $data) ? $data['type'] : $booking->type;

        if ($positionId !== null && ($startsAt->ne($booking->starts_at) || $endsAt->ne($booking->ends_at) || $positionId !== $booking->position_id)) {
            $this->validateOverlap($startsAt, $endsAt, $positionId, $booking->id);
        }

        if ($memberId !== null && ($startsAt->ne($booking->starts_at) || $endsAt->ne($booking->ends_at) || $memberId !== $booking->member_id)) {
            $this->validateMemberOverlap($startsAt, $endsAt, $memberId, $booking->id);
        }

        $bookingChanged = $positionId !== $booking->position_id
            || $memberId !== $booking->member_id
            || $startsAt->ne($booking->starts_at)
            || $endsAt->ne($booking->ends_at)
            || $type !== $booking->type;

        if ($memberId !== null && $positionId !== null && $type === Booking::TYPE_STANDARD && $bookingChanged) {
            $this->validateMemberQualification($memberId, $positionId);

            $this->policy->validateAdvanceBookingLimits($memberId, $startsAt, $booking->id);
            $this->policy->validateGatwickLimit($memberId, $booking->id);
            $this->policy->validateMinimumNotice($memberId, $positionId, $startsAt);
            $this->policy->validateFutureQualification($memberId, $positionId, $startsAt);
        }

        $booking->update($data);

        return $booking->fresh();
    }

    public function delete(Booking $booking): void
    {
        $booking->delete();
    }

    public function cancelCtsBooking(int $ctsBookingId, ?Booking $coreMirror = null): void
    {
        DB::connection('cts')->transaction(function () use ($ctsBookingId, $coreMirror): void {
            DB::connection('cts')->table('bookings')->where('id', $ctsBookingId)->delete();

            if ($coreMirror !== null) {
                $coreMirror->delete();
            }
        });
    }

    public function isPositionAvailable(Carbon $startsAt, Carbon $endsAt, int $positionId, ?int $excludeBookingId = null): bool
    {
        return $this->findOverlapping($startsAt, $endsAt, $positionId, $excludeBookingId)->isEmpty();
    }

    public function findOverlapping(Carbon $startsAt, Carbon $endsAt, int $positionId, ?int $excludeBookingId = null): Collection
    {
        $query = Booking::overlapping($startsAt, $endsAt, $positionId);

        if ($excludeBookingId !== null) {
            $query->where('id', '!=', $excludeBookingId);
        }

        return $query->get();
    }

    public function validateOverlap(Carbon $startsAt, Carbon $endsAt, int $positionId, ?int $excludeBookingId = null): void
    {
        if ($startsAt->greaterThanOrEqualTo($endsAt)) {
            throw new \InvalidArgumentException('Booking start time must be before end time.');
        }

        $overlapping = $this->findOverlapping($startsAt, $endsAt, $positionId, $excludeBookingId);

        if ($overlapping->isNotEmpty() || $this->ctsPositionOverlaps($startsAt, $endsAt, $positionId)) {
            throw new \RuntimeException('This position already has a booking in the requested time period.');
        }
    }

    private function ctsPositionOverlaps(Carbon $startsAt, Carbon $endsAt, int $positionId): bool
    {
        $position = Position::find($positionId);

        if (! $position) {
            return false;
        }

        $candidates = CtsBooking::query()
            ->where('position', $position->callsign)
            ->whereDate('date', '>=', $startsAt->copy()->subDay()->toDateString())
            ->whereDate('date', '<=', $endsAt->toDateString())
            ->get();

        foreach ($candidates as $cts) {
            $from = substr((string) $cts->from, 0, 5);
            $to = substr((string) $cts->to, 0, 5);
            $cStart = Carbon::parse(Carbon::parse($cts->date)->format('Y-m-d').' '.$from);
            $cEnd = Carbon::parse(Carbon::parse($cts->date)->format('Y-m-d').' '.$to);

            if ($to <= $from) {
                $cEnd->addDay();
            }

            if ($cStart->lt($endsAt) && $cEnd->gt($startsAt)) {
                return true;
            }
        }

        return false;
    }

    public function validateMemberQualification(int $memberId, int $positionId): void
    {
        $member = Account::findOrFail($memberId);
        $position = Position::findOrFail($positionId);

        $rating = (int) ($member->qualification_atc?->vatsim ?? 0);
        $maxAllowed = $rating + 1;

        $minRating = Position::minimumVatsimRatingForType((int) $position->getRawOriginal('type'));

        if ($minRating > $maxAllowed) {
            throw new \RuntimeException('You are not qualified to book this position.');
        }
    }

    public function validateMemberOverlap(Carbon $startsAt, Carbon $endsAt, int $memberId, ?int $excludeBookingId = null): void
    {
        $query = Booking::memberOverlapping($startsAt, $endsAt, $memberId);

        if ($excludeBookingId !== null) {
            $query->where('id', '!=', $excludeBookingId);
        }

        if ($query->exists()) {
            throw new \RuntimeException('You already have a booking in this time period.');
        }
    }
}
