<?php namespace App\Modules\Visittransfer\Http\Controllers\Admin;

use App\Http\Controllers\Adm\AdmController;
use App\Models\Mship\Account;
use App\Models\Statistic;
use App\Modules\Visittransfer\Http\Requests\FacilityCreateUpdateRequest;
use App\Modules\Visittransfer\Models\Application;
use Auth;
use Cache;
use Redirect;

class Reference extends AdmController
{

    public function getList($scope = "all")
    {
        $permittedScope = ["all", "pending-submission", "submitted", "under-review", "accepted", "rejected"];
        $scope = ($scope != null && in_array($scope, $permittedScope)) ? $scope : 'all';

        $references = \App\Modules\Visittransfer\Models\Reference::with("application")
                                                                 ->with("application.account")
                                                                 ->with("account");

        switch ($scope) {
            case "pending-submission":
                $this->setSubTitle("References Pending Submission");
                $references = $references->requested();
                break;
            case "submitted":
                $this->setSubTitle("Submitted References");
                $references = $references->submitted();
                break;
            case "under-review":
                $this->setSubTitle("References Under Review");
                $references = $references->underReview();
                break;
            case "accepted":
                $this->setSubTitle("Accepted References");
                $references = $references->accepted();
                break;
            case "rejected":
                $this->setSubTitle("Rejected References");
                $references = $references->rejected();
                break;
            case "all":
            default:
                $this->setSubTitle("All References");
        }

        $references = $references->paginate(50);

        return $this->viewMake("visittransfer::admin.reference.list")
                    ->with("references", $references);
    }
}
