<?php

namespace App\Repositories\Cts;

use Carbon\Carbon;
use App\Models\Cts\Booking;
use Illuminate\Support\Collection;

class BookingRepository
{
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

    private function formatBookings(Collection $bookings)
    {
        $returnData = collect();

        $bookings->each(function ($booking) use ($returnData) {
            $returnData->push(collect([
                'id' => $booking->id,
                'date' => $booking->date,
                'from' => Carbon::parse($booking->from)->format('H:i'),
                'to' => Carbon::parse($booking->to)->format('H:i'),
                'position' => $booking->position,
                'member' => $this->formatMember($booking),
                'type' => $booking->type,
            ]));
        });

        return $returnData;
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
