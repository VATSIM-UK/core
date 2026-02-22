<?php

namespace App\Http\Controllers\Api;

use App\Services\Api\PositionValidationService;
use Illuminate\Http\Request;

class ValidationsController
{
    public function __construct(private PositionValidationService $positionValidationService) {}

    public function view(Request $request)
    {
        $result = $this->positionValidationService->validatePosition($request->get('position'));

        return response()->json($result->payload, $result->statusCode);
    }
}
