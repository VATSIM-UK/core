<?php

namespace App\Http\Controllers\NetworkData;

use App\Http\Controllers\BaseController;
use App\Services\NetworkData\DashboardService;

class MainController extends BaseController
{
    public function __construct(private DashboardService $dashboardService)
    {
        parent::__construct();
    }

    public function getDashboard()
    {
        $dashboardData = $this->dashboardService->getDashboardData($this->account);

        $this->setTitle('Network Data Dashboard');

        return $this->viewMake('network-data.dashboard')
            ->with('atcSessions', $dashboardData['atcSessions'])
            ->with('pilotSessions', $dashboardData['pilotSessions']);
    }
}
