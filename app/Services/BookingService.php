<?php

declare(strict_types=1);

namespace App\Services;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;

class BookingService
{
    public function create(array $data): Booking
    {
        $this->validateOverlap(
            Carbon::parse($data['starts_at']),
            Carbon::parse($data['ends_at']),
            $data['position_id']
        );

        return Booking::create($data);
    }

    public function update(Booking $booking, array $data): Booking
    {
        $startsAt = Carbon::parse($data['starts_at'] ?? $booking->starts_at);
        $endsAt = Carbon::parse($data['ends_at'] ?? $booking->ends_at);
        $positionId = $data['position_id'] ?? $booking->position_id;

        if ($startsAt->ne($booking->starts_at) || $endsAt->ne($booking->ends_at) || $positionId !== $booking->position_id) {
            $this->validateOverlap($startsAt, $endsAt, $positionId, $booking->id);
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
}
