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
    public function route()
    {
        switch (Request::get("action")) {
            case "manuallogin":
                return \App::call("\App\Modules\Smartcars\Http\Controllers\Api\Authentication@postManual", Request::all());

            case "automaticlogin":
                return \App::call("\App\Modules\Smartcars\Http\Controllers\Api\Authentication@postAuto", Request::all());

            case "verifysession":
                return \App::call("\App\Modules\Smartcars\Http\Controllers\Api\Authentication@postVerify", Request::all());

            case "getpilotcenterdata":
                return "0,0,0,0";

            case "getairports":
                return "";

            case "getaircraft":
                return "";

            case "getbidflights":
                return "";
            case "bidonflight":
                return "";
            case "deletebidflight":
                return "";
            case "searchpireps":
                return "";
            case "getpirepdata":
                return "";
            case "searchflights":
                return "";
            case "createflight":
                return "";
            case "positionreport":
                return "";
            case "filepirep":
                return "";
            default:
                return "Script OK, Frame Version: VATSIM_UK_CUSTOM_1, Interface Version: VATSIM_UK_CUSTOM_1";
        }
    }
}

