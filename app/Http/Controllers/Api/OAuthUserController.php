<?php

namespace App\Http\Controllers\Api;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;

class OAuthUserController
{
    public function view(Request $request)
    {
        $clientId = $request->user()->oAuthToken()->client->id;

        $account = $request->user();
        $return = [];
        $return['cid'] = $account->id;
        $return['name_first'] = $account->name_first;
        $return['name_last'] = $account->name_last;
        $return['name_full'] = $account->name;

        // Let's get their email for this system (if they've got one set).
        $return['email'] = $account->email;

        $ssoEmailAssigned = $account->ssoEmails->filter(function ($ssoemail) use ($clientId) {
            return $ssoemail->sso_account_id == $clientId;
        })->values();

        if ($ssoEmailAssigned && count($ssoEmailAssigned) > 0) {
            $return['email'] = $ssoEmailAssigned[0]->email->email;
        }

        if ($account->qualifications_atc->isEmpty()) {
            $return['atc_rating'][] = 0;
            $return['atc_rating_human_short'][] = 'NA';
            $return['atc_rating_human_long'][] = 'None Awarded';
        } else {
            $return['atc_rating'] = $account->qualification_atc->vatsim;
            $return['atc_rating_human_short'] = $account->qualification_atc->name_small;
            $return['atc_rating_human_long'] = $account->qualification_atc->name_long;
            $return['atc_rating_date'] = $account->qualification_atc->pivot->created_at->toDateTimeString();
        }

        $return['pilot_ratings_bin'] = 0;
        $return['pilot_ratings'] = [];
        if ($account->qualifications_pilot->isEmpty()) {
            $return['pilot_ratings'][] = 0;
            $return['pilot_ratings_human_short'][] = 'NA';
            $return['pilot_ratings_human_long'][] = 'None Awarded';
        } else {
            foreach ($account->qualifications_pilot as $qual) {
                $e = [];
                $e['rating'] = $qual->vatsim;
                $e['human_short'] = $qual->name_small;
                $e['human_long'] = $qual->name_long;
                $e['date'] = $qual->pivot->created_at->toDateTimeString();
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
            $e['date'] = $qual->pivot->created_at->toDateTimeString();
            $return['admin_ratings'][] = (array) $e;
        }

        $return['training_pilot_ratings'] = [];
        foreach ($account->qualifications_pilot_training as $qual) {
            $e = [];
            $e['rating'] = $qual->vatsim;
            $e['human_short'] = $qual->name_small;
            $e['human_long'] = $qual->name_long;
            $e['date'] = $qual->pivot->created_at->toDateTimeString();
            $return['training_pilot_ratings'][] = (array) $e;
        }

        $return['training_atc_ratings'] = [];
        foreach ($account->qualifications_atc_training as $qual) {
            $e = [];
            $e['rating'] = $qual->vatsim;
            $e['human_short'] = $qual->name_small;
            $e['human_long'] = $qual->name_long;
            $e['date'] = $qual->pivot->created_at->toDateTimeString();
            $return['training_atc_ratings'][] = (array) $e;
        }

        $return['account_state'] = $account->states;
        $return['account_state_current'] = $account->primary_state->name;
        $return['account_status'] = $account->status;


        $return['is_banned'] = (bool) $account->is_banned;
        $return['ban_info'] = ($account->is_banned ? $account->bans->first() : null);

        $return['is_inactive'] = (bool) $account->is_inactive;
        $return['experience'] = $account->experience;
        $return['reg_date'] = $account->joined_at ? $account->joined_at->toDateTimeString() : $account->created_at;
        $return['impersonation'] = Session::get('auth_override', false);

        return Response::json(['status' => 'success', 'data' => $return]);
    }
}
