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

    public function getList()
    {
        $references = \App\Modules\Visittransfer\Models\Reference::with("application")
                                                                 ->with("application.account")
                                                                 ->with("referee")
                                                                 ->get();

        return $this->viewMake("visittransfer::admin.reference.list")
                    ->with("references", $references);
    }
}
