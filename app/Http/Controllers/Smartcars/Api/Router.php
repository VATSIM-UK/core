<?php

namespace App\Http\Controllers\Smartcars\Api;

use App;
use App\Http\Controllers\Adm\AdmController;
use App\Models\Mship\Account;
use App\Models\Smartcars\Session;
use Debugbar;
use Illuminate\Http\Request;
use Log;

class Router extends AdmController
{
    protected $pilot;

    protected $session;

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            /* @var Request $request */
            $this->pilot = Account::find($request->input('dbid'));
            $this->session = Session::findBySessionId($request->input('sessionid', null));

            return $next($request);
        });
    }

    public function routeRequest(Request $request)
    {
        Debugbar::disable();

        if (config('app.debug_smartcars')) {
            Log::info($request->method().'::'.$request->fullUrl());
            Log::info(json_encode($request->all()));
        }

        if ($request->method() == 'POST') {
            $return = $this->postRoute($request);
        } else {
            $return = $this->getRoute($request);
        }

        if (config('app.debug_smartcars')) {
            Log::info($return);
        }

        return $return;
    }

    protected function getRoute(Request $request)
    {
        switch ($request->get('action')) {
            case 'automaticlogin':
                return App::call(Authentication::class.'@postAuto');

            case 'verifysession':
                return App::call(Authentication::class.'@postVerify');

            case 'getpilotcenterdata':
                return App::call(Data::class.'@getPilotInfo');

            case 'getairports':
                return App::call(Data::class.'@getAirports');

            case 'getaircraft':
                return App::call(Data::class.'@getAircraft');

            case 'searchflights':
                return App::call(Flight::class.'@getSearch');

            case 'getbidflights':
                return App::call(Flight::class.'@getBids');

            case 'bidonflight':
                if (! $this->verify()) {
                    return 'AUTH_FAILED';
                }

                return App::call(Flight::class.'@getBid');

            case 'deletebidflight':
                if (! $this->verify()) {
                    return 'AUTH_FAILED';
                }

                return App::call(Flight::class.'@getBidDelete');

            case 'searchpireps':
                return App::call(Pirep::class.'@getSearch');

            case 'getpirepdata':
                return App::call(Pirep::class.'@getData');

            case 'createflight':
                return '';

            case 'filepirep':
                return '';

            default:
                return 'Script OK, Frame Version: VATSIM_UK_CUSTOM_1, Interface Version: VATSIM_UK_CUSTOM_1';
        }
    }

    protected function postRoute(Request $request)
    {
        switch ($request->get('action')) {
            case 'manuallogin':
                return App::call(Authentication::class.'@postManual');

            case 'searchflights':
                return App::call(Flight::class.'@getSearch');

            case 'getbidflights':
                return App::call(Flight::class.'@getBids');

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
                if (! $this->verify()) {
                    return 'AUTH_FAILED';
                }

                return App::call(Flight::class.'@postPosition');

            case 'filepirep':
                if (! $this->verify()) {
                    return 'AUTH_FAILED';
                }

                return App::call(Flight::class.'@postReport');

            default:
                return 'Script OK, Frame Version: VATSIM_UK_CUSTOM_1, Interface Version: VATSIM_UK_CUSTOM_1';
        }
    }

    protected function verify()
    {
        if ($this->session->account_id != $this->pilot->id) {
            return false;
        }

        return true;
    }
}
