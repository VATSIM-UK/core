<?php

namespace App\Http\Controllers\VisitTransfer\Site;

use App\Http\Controllers\BaseController;
use App\Services\VisitTransfer\DashboardService;

class Dashboard extends BaseController
{
    public function __construct(private DashboardService $dashboardService)
    {
        parent::__construct();
    }

    public function getDashboard()
    {
        $dashboardData = $this->dashboardService->getDashboardData($this->account);

        $this->setTitle('Visiting and Transfer Dashboard');

        return $this->viewMake('visit-transfer.site.dashboard')
            ->with('allApplications', $dashboardData['allApplications'])
            ->with('currentVisitApplication', $dashboardData['currentVisitApplication'])
            ->with('currentTransferApplication', $dashboardData['currentTransferApplication'])
            ->with('pendingReferences', $dashboardData['pendingReferences']);
    }
}
