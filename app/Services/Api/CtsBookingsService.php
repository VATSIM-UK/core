<?php

namespace App\Services\Api;

use App\Repositories\Cts\BookingRepository;
use App\Services\Api\DTO\ApiServiceResult;
use Carbon\Carbon;
use Carbon\Exceptions\InvalidFormatException;
use Illuminate\Support\Facades\RateLimiter;

class CtsBookingsService
{
    public function __construct(private BookingRepository $bookingRepository) {}


    public function getBookingsForApi(string $requestIp, ?string $requestedDate, string $bookingsRoute): ApiServiceResult
    {
        $result = $this->getBookings($requestIp, $requestedDate);

        if ($result->statusCode !== 200) {
            return $result;
        }

        $payload = $result->payload;
        $payload['next_page_url'] = $bookingsRoute.'?date='.$payload['next_date'];
        $payload['previous_page_url'] = $payload['previous_date']
            ? $bookingsRoute.'?date='.$payload['previous_date']
            : null;
        unset($payload['next_date'], $payload['previous_date']);

        return new ApiServiceResult($result->statusCode, $payload, $result->headers);
    }

    public function getBookings(string $requestIp, ?string $requestedDate): ApiServiceResult
    {
        if (app()->environment() !== 'development' && RateLimiter::tooManyAttempts('get-bookings:'.$requestIp, 1)) {
            $seconds = RateLimiter::availableIn('get-bookings:'.$requestIp);

            return new ApiServiceResult(
                429,
                ['message' => 'Too many requests. Please try again after '.$seconds.' seconds.'],
                [
                    'Retry-After' => $seconds,
                    'X-RateLimit-Reset' => now()->addSeconds($seconds)->getTimestamp(),
                ]
            );
        }

        RateLimiter::hit('get-bookings:'.$requestIp, 300);

        $date = Carbon::now()->startOfDay();

        if ($requestedDate) {
            try {
                $date = Carbon::parse($requestedDate);

                if ($date->isPast() && $date->diffInDays(Carbon::now()) > 30) {
                    return new ApiServiceResult(400, [
                        'message' => 'Date is too far in the past. Please use a date within the last 30 days. Oldest date allowed: '.Carbon::now()->subDays(30)->toDateString(),
                    ]);
                }
            } catch (InvalidFormatException) {
                return new ApiServiceResult(400, [
                    'message' => 'Invalid date format. Please use YYYY-MM-DD.',
                ]);
            }
        }

        $bookings = $this->bookingRepository->getBookings($date);

        return new ApiServiceResult(200, [
            'bookings' => $bookings->map(function ($booking) {
                return collect($booking)->except(['member'])->toArray();
            }),
            'date' => $date->toDateString(),
            'count' => $bookings->count(),
            'next_date' => $date->copy()->addDay()->toDateString(),
            'previous_date' => $this->getPreviousDate($date),
        ]);
    }

    private function getPreviousDate(Carbon $date): ?string
    {
        $previousDate = $date->copy()->subDay();

        if ($previousDate->diffInDays(Carbon::now()) > 30 && $previousDate->isPast()) {
            return null;
        }

        return $previousDate->toDateString();
    }
}
