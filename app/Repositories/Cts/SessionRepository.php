<?php

namespace App\Repositories\Cts;

use App\Models\Cts\Session;
use Carbon\Carbon;

class SessionRepository
{
    public function mentorIdsForSessionsInLast28Days()
    {
        $sessions = Session::where('taken_date', '>=', Carbon::parse("-28 days")->toDateString())
                           ->where('session_done', '=', 1)
                           ->where('noShow', '=', 0)
                           ->get();

        return $sessions->unique('mentor_id')->pluck('mentor_id')->toArray();
    }
}