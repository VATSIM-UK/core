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
            ])->setStatusCode(400);
        }

        $positions = new ValidationPositionRepository();
        $position = $positions->findByPosition($request->get('position'));

        if (empty($position)) {
            return response()->json([
                'status'  => '404',
                'message' => 'Position could not be found.'
            ])->setStatusCode(404);
        }

        $validatedMembers = $positions->getValidatedMembersFor($position);

        return response()->json([
            $validatedMembers,
            ['position' => $position->position],
        ]);
    }
}
