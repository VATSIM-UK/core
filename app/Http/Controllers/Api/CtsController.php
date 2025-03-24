<?php

namespace App\Http\Controllers\Api;

use App\Repositories\Cts\BookingRepository;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\RateLimiter;

class CtsController
{
    public function __construct(private BookingRepository $bookingRepository) {}

    public function getBookings(Request $request)
    {
        if (app()->environment() !== 'development' && RateLimiter::tooManyAttempts('get-bookings:'.$request->ip(), 1)) {
            $seconds = RateLimiter::availableIn('get-bookings:'.$request->ip());

            return response()->json([
                'message' => 'Too many requests. Please try again after '.$seconds.' seconds.',
            ], Response::HTTP_TOO_MANY_REQUESTS)
                ->header('Retry-After', $seconds)
                ->header('X-RateLimit-Reset', now()->addSeconds($seconds)->getTimestamp());
        }

        RateLimiter::hit('get-bookings:'.$request->ip(), 300); // 5 minutes

        $date = Carbon::now()->startOfDay();
        $requestedDate = $request->get('date', null);

        // Validate date, default to today
        if ($requestedDate) {
            try {
                $date = Carbon::parse($requestedDate);

                if ($date->isPast() && $date->diffInDays(Carbon::now()) > 30) {
                    return response()->json([
                        'message' => 'Date is too far in the past. Please use a date within the last 30 days. Oldest date allowed: '.Carbon::now()->subDays(30)->toDateString(),
                    ], Response::HTTP_BAD_REQUEST);
                }

            } catch (InvalidFormatException $e) {
                return response()->json([
                    'message' => 'Invalid date format. Please use YYYY-MM-DD.',
                ], Response::HTTP_BAD_REQUEST);
            }
        }

        $bookings = $this->bookingRepository->getBookings($date);

        return response()->json([
            'bookings' => $bookings->map(function ($booking) {
                return collect($booking)->except(['member'])->toArray();
            }),
            'date' => $date->toDateString(),
            'count' => $bookings->count(),
            'next_page_url' => $this->generateNextPageUrl($date),
            'previous_page_url' => $this->generatePreviousPageUrl($date),
        ]);
    }

    private function generateNextPageUrl(Carbon $date): string
    {
        return route('api.cts.bookings').'?date='.$date->copy()->addDay()->toDateString();
    }

    private function generatePreviousPageUrl(Carbon $date): ?string
    {
        $previousDate = $date->copy()->subDay();

        if ($previousDate->diffInDays(Carbon::now()) > 30 && $previousDate->isPast()) {
            return null;
        }

        return route('api.cts.bookings').'?date='.$previousDate->toDateString();
    }
}
