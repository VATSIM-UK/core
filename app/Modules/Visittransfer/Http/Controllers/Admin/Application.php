<?php namespace App\Modules\Visittransfer\Http\Controllers\Admin;

use App\Http\Controllers\Adm\AdmController;
use App\Models\Mship\Account;
use App\Models\Statistic;
use App\Modules\Visittransfer\Models\Application as ApplicationModel;
use App\Modules\Visittransfer\Models\Referee;
use Auth;
use Cache;
use Illuminate\Support\Collection;
use Input;

class Application extends AdmController
{

    public function getList($scope = "all")
    {
        $permittedScope = ['all', 'open', 'closed'];
        $scope = ($scope != null && in_array($scope, $permittedScope)) ? $scope : 'all';

        // Sorting and searching!
        $sortBy = in_array(Input::get("sort_by"),
            ["id", "account_id", "type", "created_at", "updated_at"]) ? Input::get("sort_by") : "updated_at";
        $sortDir = in_array(Input::get("sort_dir"), ["ASC", "DESC"]) ? Input::get("sort_dir") : "DESC";

        // ORM it all!
        $totalApplications = ApplicationModel::count();
        $applicationsQuery = ApplicationModel::orderBy($sortBy, $sortDir)
                                              ->with("applicant")
                                              ->with("facility")
                                              ->with("referees.account");

        switch ($scope) {
            case "open":
                $this->setTitle("Open Applications");
                $applicationsQuery = $applicationsQuery->open();
                break;
            case "closed":
                $this->setTitle("Closed Applications");
                $applicationsQuery = $applicationsQuery->closed();
                break;
            case "all":
            default:
                $this->setTitle("All Applications");
        }

        $applicationsQuery = $applicationsQuery->paginate(50);

        // Now we need to prepare the collection as a result for the view!
        $applications = new Collection();
        foreach ($applicationsQuery as $a) {
            $applications->prepend($a);
        }
        $applications = $applications->reverse();

        $this->_pageSubTitle = "Visiting &amp; Transferring";
        return $this->viewMake("visittransfer::admin.application.list")
                    ->with("applications", $applications)
                    ->with("applicationsQuery", $applicationsQuery)
                    ->with("sortBy", $sortBy)
                    ->with("sortDir", $sortDir)
                    ->with("sortDirSwitch", ($sortDir == "DESC" ? "ASC" : "DESC"));
    }
}
