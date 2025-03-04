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

    public function getBookings()
    {
        $bookings = $this->bookingRepository->getBookings();

        return response()->json($bookings);
    }
}
