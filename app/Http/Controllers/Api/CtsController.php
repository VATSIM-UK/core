<?php

namespace App\Http\Controllers\Api;

use App\Repositories\Cts\BookingRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Response;

class CtsController
{
    public function __construct(private BookingRepository $bookingRepository)
    {}

    public function getBookings(Request $request)
    {
        if (app()->environment() !== 'development' && RateLimiter::tooManyAttempts('get-bookings:'.$request->ip(), 1)) {
            $seconds = RateLimiter::availableIn('get-bookings:'.$request->ip());
            return response()->json([
            'message' => 'Too many requests. Please try again after '.$seconds.' seconds.'
            ], Response::HTTP_TOO_MANY_REQUESTS)
            ->header('Retry-After', $seconds)
            ->header('X-RateLimit-Reset', now()->addSeconds($seconds)->getTimestamp());
        }

        RateLimiter::hit('get-bookings:'.$request->ip(), 300); // 5 minutes

        $bookings = $this->bookingRepository->getBookings(50);

        if ($bookings->isEmpty()) {
            return response()->json(['message' => 'No bookings found'], 404);
        }

        // Not all bookings from the min/max date may be returned if the pagination limit is reached
        $fromDate = $bookings->first()->date;
        $toDate = $bookings->last()->date;

        return response()->json([
            'bookings' => $bookings,
            'meta' => [
                'dateRange' => [
                    'from' => $fromDate,
                    'to' => $toDate,
                ],
            ],
        ]);
    }
}
