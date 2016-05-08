<?php namespace App\Modules\Visittransfer\Http\Controllers\Site;

use App\Http\Controllers\BaseController;
use App\Models\Mship\Account;
use App\Modules\Visittransfer\Models\Application;
use Auth;

class Dashboard extends BaseController {

    public function getDashboard(){
        $allApplications = Auth::user()->visitTransferApplications;

        $currentVisitApplication = Auth::user()->visitTransferApplications()->visit()->latest()->first();

        $currentTransferApplication = Auth::user()->visitTransferApplications()->transfer()->latest()->first();

        return $this->viewMake("visittransfer::site.dashboard")
                    ->with("allApplications", $allApplications)
                    ->with("currentVisitApplication", $currentVisitApplication)
                    ->with("currentTransferApplication", $currentTransferApplication);
    }

}
