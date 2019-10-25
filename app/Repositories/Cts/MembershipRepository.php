<?php

namespace App\Repositories\Cts;

use App\Models\Cts\Membership;

class MembershipRepository
{
    public function getMembersOf($rtsId)
    {
        $memberships = Membership::where('rts_id', '=', $rtsId)->get();

        $members = collect();

        foreach ($memberships as $membership) {
            $members->push($membership->member);
        }

        return $members;
    }
}
