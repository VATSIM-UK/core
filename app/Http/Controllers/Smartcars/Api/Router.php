<?php

namespace App\Http\Controllers\Smartcars\Api;

use Input;
use Request;
use App\Models\Mship\Account;
use App\Models\Smartcars\Session;
use App\Http\Controllers\Adm\AdmController;

class Router extends AdmController
{
    private $pilot = null;

    public function __construct()
    {
        $this->pilot   = Account::find(Input::get('dbid'));
        $this->session = Session::findBySessionId(Input::get('sessionid', null));
    }

    private function verify()
    {
        if ($this->session->account_id != $this->pilot->id) {
            return false;
        }

        return true;
    }

    public function getRoute()
    {
        \Debugbar::disable();
        switch (Request::get('action')) {
            case 'automaticlogin':
                return \App::call(\App\Http\Controllers\Smartcars\Api\Authentication::class.'@postAuto', Request::all());

            case 'verifysession':
                return \App::call(\App\Http\Controllers\Smartcars\Api\Authentication::class.'@postVerify', Request::all());

            case 'getpilotcenterdata':
                return \App::call(\App\Http\Controllers\Smartcars\Api\Data::class.'@getPilotInfo', Request::all());

            case 'getairports':
                return \App::call(\App\Http\Controllers\Smartcars\Api\Data::class.'@getAirports', Request::all());

            case 'getaircraft':
                return \App::call(\App\Http\Controllers\Smartcars\Api\Data::class.'@getAircraft', Request::all());

            case 'searchflights':
                return \App::call(\App\Http\Controllers\Smartcars\Api\Flight::class.'@getSearch', Request::all());

            case 'getbidflights':
                return \App::call(\App\Http\Controllers\Smartcars\Api\Flight::class.'@getBids', Request::all());

            case 'bidonflight':
                if (!$this->verify()) {
                    return 'AUTH_FAILED';
                }

                return \App::call(\App\Http\Controllers\Smartcars\Api\Flight::class.'@getBid', Request::all());

            case 'deletebidflight':
                if (!$this->verify()) {
                    return 'AUTH_FAILED';
                }

                return \App::call(\App\Http\Controllers\Smartcars\Api\Flight::class.'@getBidDelete', Request::all());

            case 'searchpireps':
                return \App::call(\App\Http\Controllers\Smartcars\Api\Pirep::class.'@getSearch', Request::all());

            case 'getpirepdata':
                return \App::call(\App\Http\Controllers\Smartcars\Api\Pirep::class.'@getData', Request::all());

            case 'createflight':
                return '';

            case 'filepirep':
                return '';

            default:
                return 'Script OK, Frame Version: VATSIM_UK_CUSTOM_1, Interface Version: VATSIM_UK_CUSTOM_1';
        }
    }

    public function postRoute()
    {
        \Debugbar::disable();
        switch (Request::get('action')) {
            case 'manuallogin':
                return \App::call(\App\Http\Controllers\Smartcars\Api\Authentication::class.'@postManual', Request::all());

            case 'searchflights':
                return \App::call(\App\Http\Controllers\Smartcars\Api\Flight::class.'@getSearch', Request::all());

            case 'getbidflights':
                return \App::call(\App\Http\Controllers\Smartcars\Api\Flight::class.'@getBids', Request::all());

            case 'bidonflight':
                return '';

            case 'deletebidflight':
                return '';

            case 'searchpireps':
                return '';

            case 'getpirepdata':
                return '';

            case 'createflight':
                return '';

            case 'positionreport':
                if (!$this->verify()) {
                    return 'AUTH_FAILED';
                }

                return \App::call(\App\Http\Controllers\Smartcars\Api\Flight::class.'@postPosition', Request::all());

            case 'filepirep':
                if (!$this->verify()) {
                    return 'AUTH_FAILED';
                }

                return \App::call(\App\Http\Controllers\Smartcars\Api\Flight::class.'@postReport', Request::all());

            default:
                return 'Script OK, Frame Version: VATSIM_UK_CUSTOM_1, Interface Version: VATSIM_UK_CUSTOM_1';
        }
    }
}
