<?php

namespace App\Http\Controllers\VisitTransferLegacy\Site;

use App\Http\Controllers\BaseController;
use Auth;

class Dashboard extends BaseController
{
    public function getDashboard()
    {
        $allApplications = Auth::user()->visitTransferApplications;

        $currentVisitApplication = Auth::user()->visitTransferApplications()->visit()->open()->latest()->first();

        $currentTransferApplication = Auth::user()->visitTransferApplications()->transfer()->open()->latest()->first();

        $pendingReferences = $this->account->visitTransferReferee->filter(function ($ref) {
            return $ref->is_requested;
        });

        $this->setTitle('Visiting and Transfer Dashboard');

        return $this->viewMake('visit-transfer.site.dashboard')
            ->with('allApplications', $allApplications)
            ->with('currentVisitApplication', $currentVisitApplication)
            ->with('currentTransferApplication', $currentTransferApplication)
            ->with('pendingReferences', $pendingReferences);
    }
}
