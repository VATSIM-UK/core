<?php


namespace App\Repositories\Cts;


use App\Models\Cts\Member;
use App\Models\Cts\Session;
use Illuminate\Database\Eloquent\Collection;

class SessionRepository
{
    public function getSessionsByRts(int $rts): Collection
    {
        return Session::where('rts_id', $rts)->get();
    }

    public function getSessionsForMemberByRts(Member $member, $rts): Collection
    {
        return  $this->getSessionsByRts($rts)->where('student_id', $member->id);
    }
}
