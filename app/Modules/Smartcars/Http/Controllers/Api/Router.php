<?php namespace App\Modules\Smartcars\Http\Controllers\Api;

use App\Http\Controllers\Adm\AdmController;
use App\Models\Mship\Account;
use App\Models\Statistic;
use App\Modules\Smartcars\Models\Session;
use App\Modules\Visittransfer\Models\Application;
use App\Modules\Visittransfer\Models\Reference;
use Auth;
use Cache;
use Input;
use Request;

class Router extends AdmController
{
    public function route(){
        switch(Request::get("action")){
            case "manuallogin":
                \App::call("\App\Modules\Smartcars\Http\Controllers\Api\Authentication@postManual", Request::all());

            case "automaticlogin":
                \App::call("\App\Modules\Smartcars\Http\Controllers\Api\Authentication@postAuto", Request::all());

            case "verifysession":
                \App::call("\App\Modules\Smartcars\Http\Controllers\Api\Authentication@postVerify", Request::all());

            case "getpilotcenterdata":
                return "0,0,0,0";

            case "getairports":
                return null;
            case "getaircraft":
                return null;
            case "getbidflights":
                return null;
            case "bidonflight":
                return null;
            case "deletebidflight":
                return null;
            case "searchpireps":
                return null;
            case "getpirepdata":
                return null;
            case "searchflights":
                return null;
            case "createflight":
                return null;
            case "positionreport":
                return null;
            case "filepirep":
                return null;
            default:
                return "Script OK, Frame Version: VATSIM_UK_CUSTOM_1, Interface Version: VATSIM_UK_CUSTOM_1";
    }
}
