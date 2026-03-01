<?php

namespace App\Http\Controllers\Api;

use App\Services\Api\CtsBookingsService;
use Illuminate\Http\Request;

class CtsController
{
    public function __construct(private CtsBookingsService $ctsBookingsService) {}

    public function getBookings(Request $request)
    {
        $result = $this->ctsBookingsService->getBookingsForApi(
            (string) $request->ip(),
            $request->get('date'),
            route('api.cts.bookings')
        );

        $response = response()->json($result->payload, $result->statusCode);

        foreach ($result->headers as $header => $value) {
            $response->header($header, $value);
        }

        return $response;
    }
}
