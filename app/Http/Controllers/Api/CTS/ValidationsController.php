<?php

namespace App\Http\Controllers\Api\CTS;

use App\Models\Cts\ValidationPosition;
use App\Repositories\Cts\ValidationPositionRepository;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class ValidationsController
{
    public function view(Request $request)
    {
        if (! $request->get('position')) {
            return response()->json([
                'status' => '400',
                'message' => 'No position was supplied.',
            ], 400);
        }

        try {
            $position = (new ValidationPositionRepository)->findByPosition($request->get('position'));
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => '404',
                'message' => 'Position could not be found.',
            ], 404);
        }

        return response()->json([
            'status' => ['position' => $position->position],
            'validated_members' => $this->getValidatedMembers($position),
        ]);
    }

    private function getValidatedMembers(ValidationPosition $position)
    {
        $cacheKey = "validation_members_{$position->id}";

        if (cache($cacheKey)) {
            return cache($cacheKey);
        }

        $members = (new ValidationPositionRepository)->getValidatedMembersFor($position);

        cache([$cacheKey, $members], now()->addMinutes(30));

        return $members;
    }
}
