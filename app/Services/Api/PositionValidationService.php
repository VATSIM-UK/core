<?php

namespace App\Services\Api;

use App\Models\Atc\Position;
use App\Models\Roster;
use App\Services\Api\DTO\ApiServiceResult;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class PositionValidationService
{
    public function validatePosition(?string $positionCallsign): ApiServiceResult
    {
        if (! $positionCallsign) {
            return new ApiServiceResult(400, [
                'status' => '400',
                'message' => 'No position was supplied.',
            ]);
        }

        try {
            $position = Position::where('callsign', $positionCallsign)->firstOrFail();
        } catch (ModelNotFoundException) {
            return new ApiServiceResult(404, [
                'status' => '404',
                'message' => 'Position not found.',
            ]);
        }

        $validatedMembers = cache()->remember("validation_members_{$position->id}", now()->addDay(), function () use ($position) {
            return Roster::all()->filter(function (Roster $roster) use ($position) {
                return $roster->accountCanControl($position);
            })->values()->map(function (Roster $roster) {
                return ['id' => $roster->account_id];
            });
        });

        return new ApiServiceResult(200, [
            'status' => ['position' => $position->name],
            'validated_members' => $validatedMembers,
        ]);
    }
}
