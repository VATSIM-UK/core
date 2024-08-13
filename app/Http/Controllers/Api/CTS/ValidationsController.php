<?php

namespace App\Http\Controllers\Api\CTS;

use App\Models\Atc\Position;
use App\Models\Roster;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

class ValidationsController
{
    public function view(Request $request)
    {
        if (!$request->get('position')) {
            return response()->json([
                'status' => '400',
                'message' => 'No position was supplied.',
            ], 400);
        }

        try {
            $position = Position::where('callsign', $request->get('position'))->firstOrFail();
        } catch (ModelNotFoundException $e) {
            return response()->json([
                'status' => '404',
                'message' => 'Position not found.',
            ], 404);
        }

        $validatedMembers = cache()->remember("validation_members_{$position->id}", now()->addMinutes(30), function () use ($position) {
            return Roster::all()->filter(function (Roster $roster) use ($position) {
                return $roster->accountCanControl($position);
            })->pluck('account_id');
        });

        return response()->json([
            'status' => ['position' => $position->name],
            'validated_members' => $validatedMembers,
        ]);
    }
}
