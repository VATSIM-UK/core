<?php

namespace App\Repositories\Cts;

use App\Models\Cts\Membership;
use Illuminate\Database\Eloquent\Collection;

class MembershipRepository
{
    public function getMembersOf(int $rtsId): Collection
    {
        return Membership::where('rts_id', '=', $rtsId)->get()->map(function ($membership) {
            return $membership->member;
        });
    }

    public function getActiveMembersOfRts(int $rtsId): Collection
    {
        return Membership::where('rts_id', $rtsId)->where('type', 'H')->get();
    }
}
