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

class Authentication extends AdmController
{
    private function preparePilotInfo($account, $session){
        $info = [];

        $info['dbid'] = $account->id;
        $info['code'] = "PTE";
        $info['pilotid'] = $account->id;
        $info['sessionid'] = $session->session_id;
        $info['firstname'] = $account->name_first;
        $info['lastname'] = $account->name_last;
        $info['email'] = $account->email;
        $info['ranklevel'] = 1;
        $info['rankstring'] = $account->qualifications_pilot_string;

        return $info;
    }

    public function postManual(){
        Session::deleteOldSessions();

        $account = Account::findOrRetrieve(Input::get("userid"));

        if($account->is_banned){
            return "ACCOUNT_INACTIVE";
        }

        $passwordOK = $account->verifyPassword(Input::get("password"));

        if($account && $account->hasPassword() && $passwordOK){
            $session = Session::create(["account_id" => $account->id, "session_id" => Input::get("sessionid")]);

            return response()->csv($this->preparePilotInfo($session->account, $session));
        }

        return "AUTH_FAILED";
    }

    public function postAuto(){
        Session::deleteOldSessions();

        $session = Session::sessionId(Input::get("oldsessionid"))->accountId(Input::get("dbid"))->first();

        if($session){

            if($session->account->is_banned){
                return "ACCOUNT_INACTIVE";
            }

            $session->session_id = Input::get("sessionid");
            $session->save();

            return response()->csv($this->preparePilotInfo($session->account, $session));
        }

        return "AUTH_FAILED";
    }

    public function postVerify(){
        Session::deleteOldSessions();

        $session = Session::sessionId(Input::get("sessionid"))->accountId(Input::get("dbid"))->first();

        if($session){
            return response()->csv([$session->session_id, $session->account->name_first, $session->account->name_last]);
        }

        return "AUTH_FAILED";
    }
}
