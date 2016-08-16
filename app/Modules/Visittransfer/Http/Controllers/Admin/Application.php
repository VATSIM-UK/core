<?php namespace App\Modules\Visittransfer\Http\Controllers\Admin;

use App\Http\Controllers\Adm\AdmController;
use App\Models\Mship\Account;
use App\Models\Statistic;
use App\Modules\Visittransfer\Models\Application as ApplicationModel;
use App\Modules\Visittransfer\Models\Reference;
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
        $applications = ApplicationModel::orderBy($sortBy, $sortDir)
                                              ->with("account")
                                              ->with("facility")
                                              ->with("referees");

        switch ($scope) {
            case "open":
                $this->setSubTitle("Open Applications");
                $applications = $applications->open();
                break;
            case "closed":
                $this->setSubTitle("Closed Applications");
                $applications = $applications->closed();
                break;
            case "all":
            default:
                $this->setSubTitle("All Applications");
        }

        $applications = $applications->paginate(50);

        return $this->viewMake("visittransfer::admin.application.list")
                    ->with("applications", $applications)
                    ->with("sortBy", $sortBy)
                    ->with("sortDir", $sortDir)
                    ->with("sortDirSwitch", ($sortDir == "DESC" ? "ASC" : "DESC"));
    }
}
