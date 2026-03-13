<?php

namespace App\Http\Controllers\VisitTransfer\Site;

use App\Http\Controllers\BaseController;
use Auth;

class Dashboard extends BaseController
{
    public function getDashboard()
    {
        $allApplications = Auth::user()->visitTransferApplications;

        $currentVisitApplication = Auth::user()->visitTransferApplications()->visit()->open()->latest()->first();

        $currentTransferApplication = Auth::user()->visitTransferApplications()->transfer()->open()->latest()->first();

        $this->setTitle('Visiting and Transfer Dashboard');

        return $this->viewMake('visit-transfer.site.dashboard')
            ->with('allApplications', $allApplications)
            ->with('currentVisitApplication', $currentVisitApplication)
            ->with('currentTransferApplication', $currentTransferApplication);
    }
}
