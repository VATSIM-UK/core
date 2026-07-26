<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Atc\Position;
use App\Models\Booking;
use App\Models\Mship\Account;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class BookingService
{
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

            if ($data['position_id'] !== null) {
                $this->validateMemberQualification(
                    $data['member_id'],
                    $data['position_id']
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

        if ($positionId !== null && ($startsAt->ne($booking->starts_at) || $endsAt->ne($booking->ends_at) || $positionId !== $booking->position_id)) {
            $this->validateOverlap($startsAt, $endsAt, $positionId, $booking->id);
        }

        if ($memberId !== null && ($startsAt->ne($booking->starts_at) || $endsAt->ne($booking->ends_at) || $memberId !== $booking->member_id)) {
            $this->validateMemberOverlap($startsAt, $endsAt, $memberId, $booking->id);
        }

        if ($memberId !== null && $positionId !== null && ($positionId !== $booking->position_id || $memberId !== $booking->member_id)) {
            $this->validateMemberQualification($memberId, $positionId);
        }

        $booking->update($data);

        return $booking->fresh();
    }

    public function delete(Booking $booking): void
    {
        $booking->delete();
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

        if ($overlapping->isNotEmpty()) {
            throw new \RuntimeException('This position already has a booking in the requested time period.');
        }
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
