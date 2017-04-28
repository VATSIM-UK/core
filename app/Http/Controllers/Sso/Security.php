<?php

namespace App\Http\Controllers\Sso;

use Input;
use Session;
use Response;
use App\Models\Sso\Token;
use App\Models\Sso\Account;
use App\Models\Mship\Account as MemberAccount;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class Security extends \App\Http\Controllers\BaseController
{
    private $ssoAccount;

    public function postGenerate()
    {
        if ($x = $this->security()) {
            return $x;
        }

        // Return URL must be provided!
        if (!Input::get('return_url', false)) {
            return Response::json(['status' => 'error', 'error' => 'NO_RETURN_URL']);
        }

        $token = new Token();
        $_t = sha1(uniqid($this->ssoAccount->username, true));
        $token->token = md5($_t.$this->ssoAccount->api_key_private);
        $token->request_ip = \Request::ip();
        $token->return_url = Input::get('return_url');
        $token->created_at = \Carbon\Carbon::now()->toDateTimeString();
        $token->expires_at = \Carbon\Carbon::now()->addMinutes(30)->toDateTimeString();
        $this->ssoAccount->tokens()->save($token);

        // We want to return the token to the user for later use in their requests.
        return Response::json(['status' => 'success', 'token' => $_t, 'timestamp' => strtotime($token->created_at)]);
    }

    public function postDetails()
    {
        if ($x = $this->security()) {
            return $x;
        }

        // Did we receive a token?  If we didn't get rid of them!
        if (!Input::get('access_token', false)) {
            die('SOME GENERIC ERROR');
        }

        // Check expired/invalid
        $accessToken = Input::get('access_token');
        try {
            $accessToken = Token::tokenValue($accessToken)->valid()->firstOrFail();
        } catch (ModelNotFoundException $e) {
            die('TOKEN NOT FOUND');
        }

        $accessToken->used_at = \Carbon\Carbon::now()->toDateTimeString();
        $accessToken->expires_at = \Carbon\Carbon::now()->toDateTimeString();
        $accessToken->save();

        // Create the response...
        $account = MemberAccount::find($accessToken->account_id);

        if (!$account) {
            return Response::json(['status' => 'error', 'error' => 'NO_AUTHORISED_ACCOUNT']);
        }

        $return = [];
        $return['cid'] = $account->id;
        $return['name_first'] = $account->name_first;
        $return['name_last'] = $account->name_last;
        $return['name_full'] = $account->name;

        // Let's get their email for this system (if they've got one set).
        $return['email'] = $account->email;

        $ssoEmailAssigned = $account->ssoEmails->filter(function ($ssoemail) use ($accessToken) {
            return $ssoemail->sso_account_id == $accessToken->sso_account_id;
        })->values();

        if ($ssoEmailAssigned && count($ssoEmailAssigned) > 0) {
            $return['email'] = $ssoEmailAssigned[0]->email->email;
        }

        $return['atc_rating'] = $account->qualification_atc->vatsim;
        $return['atc_rating_human_short'] = $account->qualification_atc->name_small;
        $return['atc_rating_human_long'] = $account->qualification_atc->name_long;
        $return['atc_rating_date'] = $account->qualification_atc->created_at->toDateTimeString();

        $return['pilot_ratings_bin'] = 0;
        $return['pilot_ratings'] = [];
        if (count($account->qualifications_pilot) < 1) {
            $return['pilot_ratings'][] = 0;
            $return['pilot_ratings_human_short'][] = 'NA';
            $return['pilot_ratings_human_long'][] = 'None Awarded';
        } else {
            foreach ($account->qualifications_pilot as $qual) {
                $e = [];
                $e['rating'] = $qual->vatsim;
                $e['human_short'] = $qual->name_small;
                $e['human_long'] = $qual->name_long;
                $e['date'] = $qual->created_at->toDateTimeString();
                $return['pilot_ratings'][] = (array) $e;
                $return['pilot_ratings_bin'] += $qual->vatsim;
            }
        }
        $return['pilot_ratings_bin'] = decbin($return['pilot_ratings_bin']);

        $return['admin_ratings'] = [];
        foreach ($account->qualifications_admin as $qual) {
            $e = [];
            $e['rating'] = $qual->vatsim;
            $e['human_short'] = $qual->name_small;
            $e['human_long'] = $qual->name_long;
            $e['date'] = $qual->created_at->toDateTimeString();
            $return['admin_ratings'][] = (array) $e;
        }

        $return['training_pilot_ratings'] = [];
        foreach ($account->qualifications_pilot_training as $qual) {
            $e = [];
            $e['rating'] = $qual->vatsim;
            $e['human_short'] = $qual->name_small;
            $e['human_long'] = $qual->name_long;
            $e['date'] = $qual->created_at->toDateTimeString();
            $return['training_pilot_ratings'][] = (array) $e;
        }

        $return['training_atc_ratings'] = [];
        foreach ($account->qualifications_atc_training as $qual) {
            $e = [];
            $e['rating'] = $qual->vatsim;
            $e['human_short'] = $qual->name_small;
            $e['human_long'] = $qual->name_long;
            $e['date'] = $qual->created_at->toDateTimeString();
            $return['training_atc_ratings'][] = (array) $e;
        }

        $return['account_state'] = $account->states;
        $return['account_state_current'] = $account->primary_state->name;
        $return['account_status'] = $account->status;
        $return['is_invisible'] = boolval($account->is_invisible);

        $return['is_banned'] = boolval($account->is_banned);
        $return['ban_info'] = ($account->is_banned ? $account->bans->first() : null);

        $return['is_inactive'] = boolval($account->is_inactive);
        $return['experience'] = $account->experience;
        $return['reg_date'] = $account->joined_at->toDateTimeString();
        $return['impersonation'] = Session::get('auth_override', false);

        // We want to return the token to the user for later use in their requests.
        return Response::json(['status' => 'success', 'data' => $return]);
    }

    private function security()
    {
        if (!Input::get('username', false)) {
            return Response::json(['status' => 'error', 'error' => 'NO_USERNAME']);
        }

        if (!Input::get('apikey_pub', false)) {
            return Response::json(['status' => 'error', 'error' => 'NO_APIKEY_PUB']);
        }

        // Authenticate....
        try {
            $this->ssoAccount = Account::where('username', '=', Input::get('username'))
                    ->where('api_key_public', '=', Input::get('apikey_pub'))
                    ->first();
        } catch (Exception $e) {
            return Response::json(['status' => 'error', 'error' => 'INVALID_CREDENTIALS']);
        }

        if (is_null($this->ssoAccount)) {
            return Response::json(['status' => 'error', 'error' => 'INVALID_CREDENTIALS']);
        }
    }
}
