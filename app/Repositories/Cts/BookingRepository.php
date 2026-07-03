<?php

declare(strict_types=1);

namespace App\Repositories\Cts;

use App\Models\Booking;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class BookingRepository
{
    private const TYPE_MAP = [
        Booking::TYPE_STANDARD => 'BK',
        Booking::TYPE_EXAM => 'EX',
        Booking::TYPE_MENTORING => 'ME',
        Booking::TYPE_EVENT => 'EV',
        Booking::TYPE_GROUP_SEMINAR => 'GS',
    ];

    public function getBookings(Carbon $date): Collection
    {
        $bookings = Booking::whereDate('starts_at', $date->toDateString())
            ->with('member')
            ->orderBy('starts_at')
            ->get();

        return $this->formatBookings($bookings);
    }

    public function getTodaysBookings(): Collection
    {
        return $this->getBookings(Carbon::now());
    }

    public function getTodaysLiveAtcBookings(): Collection
    {
        $bookings = Booking::whereDate('starts_at', Carbon::now()->toDateString())
            ->liveAtc()
            ->with('member')
            ->orderBy('starts_at')
            ->get();

        return $this->formatBookings($bookings);
    }

    public function getTodaysLiveAtcBookingsWithoutEvents(): Collection
    {
        $bookings = Booking::whereDate('starts_at', Carbon::now()->toDateString())
            ->liveAtc()
            ->notEvent()
            ->with('member')
            ->orderBy('starts_at')
            ->get();

        return $this->formatBookings($bookings);
    }

    private function formatBookings(Collection $bookings): Collection
    {
        return $bookings->map(function (Booking $booking) {
            return (object) [
                'id' => (string) $booking->id,
                'date' => $booking->starts_at->format('Y-m-d'),
                'from' => $booking->starts_at->format('H:i'),
                'to' => $booking->ends_at->format('H:i'),
                'position' => $booking->position?->callsign,
                'type' => self::TYPE_MAP[$booking->type] ?? 'BK',
                'member' => $this->formatMember($booking),
            ];
        });
    }

    private function formatMember(Booking $booking): array
    {
        if ($booking->type === Booking::TYPE_EXAM) {
            return [
                'id' => '',
                'name' => 'Hidden',
            ];
        }

        if (! $booking->member) {
            return [
                'id' => '',
                'name' => 'Unknown',
            ];
        }

        return [
            'id' => (string) $booking->member->id,
            'name' => $booking->member->name,
        ];
    }
}
