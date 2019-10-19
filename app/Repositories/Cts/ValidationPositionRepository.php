<?php

namespace App\Repositories\Cts;

use App\Models\Cts\Member;
use App\Models\Cts\ValidationPosition;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ValidationPositionRepository
{
    public function findByPositionId(int $id)
    {
        return ValidationPosition::find($id);
    }

    public function findByPosition(string $position)
    {
        return ValidationPosition::where('position', 'like', "%{$position}%")->first();
    }

    public function getValidatedMembersFor(ValidationPosition $validationPosition)
    {
        return $validationPosition->members->map(function ($member) {
            return ['id' => $member->cid, 'name' => $member->name];
        });
    }
}
