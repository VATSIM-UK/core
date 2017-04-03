<?php

namespace App\Modules\Visittransfer\Http\Controllers\Site;

use Auth;
use App\Http\Controllers\BaseController;

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

        return $this->viewMake('visittransfer::site.dashboard')
                    ->with('allApplications', $allApplications)
                    ->with('currentVisitApplication', $currentVisitApplication)
                    ->with('currentTransferApplication', $currentTransferApplication)
                    ->with('pendingReferences', $pendingReferences);
    }
}
