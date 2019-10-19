<?php

namespace App\Http\Controllers\Api\CTS;

use App\Repositories\Cts\ValidationPositionRepository;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ValidationsController
{
    public function view(Request $request)
    {
        if (!$request->get('position')) {
            return response()->json([
                'status'  => '400',
                'message' => 'No position was supplied.'
            ],400);
        }

        $position = (new ValidationPositionRepository())->findByPosition($request->get('position'));

        if (empty($position)) {
            return response()->json([
                'status'  => '404',
                'message' => 'Position could not be found.'
            ], 404);
        }

        $validatedMembers = (new ValidationPositionRepository())->getValidatedMembersFor($position);

        return response()->json([
            $validatedMembers,
            ['position' => $position->position],
        ]);
    }
}
