<?php

namespace App\Http\Controllers\Api;

use App\Services\Api\CtsBookingsService;
use Illuminate\Http\Request;

class CtsController
{
    public function __construct(private CtsBookingsService $ctsBookingsService) {}

    public function getBookings(Request $request)
    {
        $result = $this->ctsBookingsService->getBookings((string) $request->ip(), $request->get('date'));

        $payload = $result->payload;

        if ($result->statusCode === 200) {
            $payload['next_page_url'] = route('api.cts.bookings').'?date='.$payload['next_date'];
            $payload['previous_page_url'] = $payload['previous_date']
                ? route('api.cts.bookings').'?date='.$payload['previous_date']
                : null;
            unset($payload['next_date'], $payload['previous_date']);
        }

        $response = response()->json($payload, $result->statusCode);

        foreach ($result->headers as $header => $value) {
            $response->header($header, $value);
        }

        return $response;
    }
}
