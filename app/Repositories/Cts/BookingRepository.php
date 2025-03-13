<?php

namespace App\Repositories\Cts;

use App\Models\Cts\Booking;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class BookingRepository
{
    public function getBookings(Carbon $date)
    {
        $bookings = Booking::where('date', '=', $date->toDateString())
            ->with('member')
            ->orderBy('from')
            ->get();

        return $this->formatBookings($bookings);
    }

    public function getTodaysBookings()
    {
        $bookings = Booking::where('date', '=', Carbon::now()->toDateString())
            ->with('member')
            ->orderBy('from')
            ->get();

        return $this->formatBookings($bookings);
    }

    public function getTodaysLiveAtcBookings()
    {
        $bookings = Booking::where('date', '=', Carbon::now()->toDateString())
            ->networkAtc()
            ->with('member')
            ->orderBy('from')
            ->get();

        return $this->formatBookings($bookings);
    }

    public function getTodaysLiveAtcBookingsWithoutEvents()
    {
        $bookings = Booking::where('date', '=', Carbon::now()->toDateString())
            ->notEvent()
            ->networkAtc()
            ->with('member')
            ->orderBy('from')
            ->get();

        return $this->formatBookings($bookings);
    }

    private function formatBookings(Collection $bookings)
    {
        $bookings->transform(function ($booking) {
            $booking->from = Carbon::parse($booking->from)->format('H:i');
            $booking->to = Carbon::parse($booking->to)->format('H:i');

            $booking->member = $this->formatMember($booking);
            $booking->unsetRelation('member');

            return $booking;
        });

        return $bookings;
    }

    private function formatMember(Booking $booking)
    {
        if ($booking->type == 'EX') {
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
            'id' => $booking->member->cid,
            'name' => $booking->member->name,
        ];
    }
}
