<?php


namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use App\Repositories\Cts\BookingRepository;

class CtsController
{
    private $bookingRepository;

    public function __construct(BookingRepository $bookingRepository)
    {
        $this->bookingRepository = $bookingRepository;
    }

    public function getBookings(Request $request)
    {
        $bookings = $this->bookingRepository->getBookings(50);

        if ($bookings->isEmpty()) {
            return response()->json(['message' => 'No bookings found'], 404);
        }

        $fromDate = $bookings->first()->date;
        $toDate = $bookings->last()->date;

        return response()->json([
            'bookings' => $bookings,
            'meta' => [
                'dateRange' => [
                    'from' => $fromDate,
                    'to' => $toDate
                ]
            ]
        ]);
    }
}
