<?php


namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use App\Repositories\Cts\BookingRepository;

class CtsController
{
    public static $MAX_PER_PAGE = 50;

    private $bookingRepository;

    public function __construct(BookingRepository $bookingRepository)
    {
        $this->bookingRepository = $bookingRepository;
    }

    public static function normalizePageResultCount($perPage)
    {
        return min($perPage, CtsController::$MAX_PER_PAGE);
    }


    public function getBookings(Request $request)
    {
        $perPage = self::normalizePageResultCount($request->query('count', 30));

        $bookings = $this->bookingRepository->getBookings($perPage);

        return response()->json($bookings);
    }
}
