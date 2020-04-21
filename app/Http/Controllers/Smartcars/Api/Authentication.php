<?php

namespace App\Http\Controllers\Smartcars\Api;

use App\Exceptions\Mship\InvalidCIDException;
use App\Http\Controllers\Adm\AdmController;
use App\Models\Mship\Account;
use App\Models\Smartcars\Session;
use Illuminate\Support\Facades\Request;

class Authentication extends AdmController
{
    private function preparePilotInfo($account, $session)
    {
        $info = [];

        $info['dbid'] = $account->id;
        $info['code'] = 'PTE';
        $info['pilotid'] = $account->id;
        $info['sessionid'] = $session->session_id;
        $info['firstname'] = $account->name_first;
        $info['lastname'] = $account->name_last;
        $info['email'] = $account->email;
        $info['ranklevel'] = 1;
        $info['rankstring'] = str_replace(',', '-', $account->qualifications_pilot_string);

        return $info;
    }

    public function postManual()
    {
        Session::deleteOldSessions();

        try {
            $account = Account::findOrRetrieve(Request::input('userid'));
        } catch (InvalidCIDException $e) {
            return 'AUTH_FAILED';
        }

        if ($account == null) {
            return 'AUTH_FAILED';
        }

        if ($account->is_banned) {
            return 'ACCOUNT_INACTIVE';
        }

        $passwordOK = $account->verifyPassword(Request::input('password'));

        if ($account->hasPassword() && $passwordOK) {
            $session = Session::create(['account_id' => $account->id, 'session_id' => Request::input('sessionid')]);

            return response()->csv($this->preparePilotInfo($session->account, $session));
        }

        return 'AUTH_FAILED';
    }

    public function postAuto()
    {
        Session::deleteOldSessions();

        $session = Session::sessionId(Request::input('oldsessionid'))->accountId(Request::input('dbid'))->first();

        if ($session) {
            if ($session->account->is_banned) {
                return 'ACCOUNT_INACTIVE';
            }

            $session->session_id = Request::input('sessionid');
            $session->save();

            return response()->csv($this->preparePilotInfo($session->account, $session));
        }

        return 'AUTH_FAILED';
    }

    public function postVerify()
    {
        Session::deleteOldSessions();

        $session = Session::sessionId(Request::input('sessionid'))->accountId(Request::input('dbid'))->first();

        if ($session) {
            return response()->csv([$session->session_id, $session->account->name_first, $session->account->name_last]);
        }

        return 'AUTH_FAILED';
    }
}
