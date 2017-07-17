<?php

namespace App\Http\Controllers\NetworkData;

use App\Http\Controllers\BaseController;

class MainController extends BaseController
{
    public function getDashboard()
    {
        $atcSessions = $this->account->networkDataAtc()->whereNotNull('disconnected_at')->orderBy('created_at', 'DESC')->paginate(20);

        return $this->viewMake('network-data.dashboard')->with('atcSessions', $atcSessions);
    }
}
