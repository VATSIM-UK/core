<?php

namespace App\Repositories\Cts;

use App\Models\Cts\ValidationPosition;

class ValidationPositionRepository
{
    public function findByPositionId(int $id)
    {
        return ValidationPosition::findOrFail($id);
    }

    public function findByPosition(string $position)
    {
        return ValidationPosition::where('position', 'like', "%{$position}%")->firstOrFail();
    }

    public function getValidatedMembersFor(ValidationPosition $validationPosition)
    {
        return $validationPosition->members->map(function ($member) {
            return ['id' => $member->cid, 'name' => $member->name];
        });
    }
}
