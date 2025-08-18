<?php

namespace App\Repositories\Cts;

use App\Models\Cts\Booking;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class BookingRepository
{
    public function getBookingsBetween($startDate, $endDate)
    {
        return Booking::with(['member', 'session.mentor'])
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()->each(function ($booking) {
                $booking->date = Carbon::parse($booking->date);
            });
    }

    public function getBookings(Carbon $date)
    {
        $bookings = Booking::where('date', '=', $date->toDateString())
            ->with(['member', 'session.mentor'])
            ->orderBy('from')
            ->get();

        return $this->formatBookings($bookings);
    }

    public function getTodaysBookings()
    {
        $bookings = Booking::where('date', '=', Carbon::now()->toDateString())
            ->with(['member', 'session.mentor'])
            ->orderBy('from')
            ->get();

        return $this->formatBookings($bookings);
    }

    public function getTodaysLiveAtcBookings()
    {
        $bookings = Booking::where('date', '=', Carbon::now()->toDateString())
            ->networkAtc()
            ->with(['member', 'session.mentor'])
            ->orderBy('from')
            ->get();

        return $this->formatBookings($bookings);
    }

    public function getTodaysLiveAtcBookingsWithoutEvents()
    {
        $bookings = Booking::where('date', '=', Carbon::now()->toDateString())
            ->notEvent()
            ->networkAtc()
            ->with(['member', 'session.mentor'])
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

            if ($booking->type === 'ME' && $booking->session) {
                $mentorName = 'Unknown';

                // Safely get mentor name and ID
                if ($booking->session->mentor) {
                    $mentorName = $booking->session->mentor->name.' ('.$booking->session->mentor->cid.')';
                }

                $booking->session_details = [
                    'id' => $booking->session->id,
                    'position' => $booking->session->position,
                    'student_id' => $booking->session->student_id,
                    'mentor_id' => $booking->session->mentor_id,
                    'mentor' => $mentorName,
                    'date' => $booking->session->date_1,
                    'from' => $booking->session->from_1,
                    'to' => $booking->session->to_1,
                    'request_time' => $booking->session->request_time,
                    'taken_time' => $booking->session->taken_time,
                ];
            }

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
