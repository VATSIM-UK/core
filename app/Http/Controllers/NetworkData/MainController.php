<?php

namespace App\Http\Controllers\NetworkData;

use App\Http\Controllers\BaseController;

class MainController extends BaseController
{
    public function getDashboard()
    {
        $atcSessions = $this->account->networkDataAtc()->offline()->orderBy('created_at', 'DESC')->paginate(20, ['*'], 'atcSessions');
        $pilotSessions = $this->account->networkDataPilot()->offline()->orderBy('created_at', 'DESC')->paginate(20, ['*'], 'pilotSessions');

        $this->setTitle('Network Data Dashboard');

        return $this->viewMake('network-data.dashboard')->with('atcSessions', $atcSessions)->with('pilotSessions', $pilotSessions);
    }
}
