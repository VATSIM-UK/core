<?php

namespace App\Http\Controllers\Adm\Sys;

use App\Models\Sys\Activity as ActivityData;

class Activity extends \App\Http\Controllers\Adm\AdmController
{
    public function getIndex()
    {
        $activities = ActivityData::orderBy('created_at', 'DESC')
                               ->with('actor')
                               ->with('subject')
                               ->limit(100)
                               ->get();

        return $this->viewMake('adm.sys.activity.list')->with('activities', $activities);
    }
}
