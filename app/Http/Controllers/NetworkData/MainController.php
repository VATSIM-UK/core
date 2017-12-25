<?php

namespace App\Http\Controllers\NetworkData;

use App\Http\Controllers\BaseController;

class MainController extends BaseController
{
    public function getDashboard()
    {
        $atcSessions = $this->account->networkDataAtc()->whereNotNull('disconnected_at')->orderBy('created_at', 'DESC')->paginate(20, ['*'], 'atcSessions');
        $pilotSessions = $this->account->networkDataPilot()->whereNotNull('disconnected_at')->orderBy('created_at', 'DESC')->paginate(20, ['*'], 'pilotSessions');

        return $this->viewMake('network-data.dashboard')->with('atcSessions', $atcSessions)->with('pilotSessions', $pilotSessions);
    }
}
