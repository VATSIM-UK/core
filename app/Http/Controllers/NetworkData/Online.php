<?php

namespace App\Http\Controllers\NetworkData;

use App\Http\Controllers\BaseController;
use App\Models\NetworkData\Atc;
use App\Models\NetworkData\Pilot;

class Online extends BaseController
{
    public function getOnline()
    {
        $atcSessions = Atc::remember(2)
            ->online()
            ->isUK()
            ->with([
                'account' => function ($q) {
                    $q->get(['name_first', 'name_last']);
                },
            ])->get();

        $pilotSessions = Pilot::remember(2)
            ->online()
            ->withinDivision()
            ->with([
                'account' => function ($q) {
                    $q->get(['name_first', 'name_last']);
                },
            ])->get();

        return $this->viewMake('network-data.site.online')
            ->with('atcSessions', $atcSessions)->with('pilotSessions', $pilotSessions);
    }
}
