<?php

namespace App\Services\Api;

use App\Models\Mship\Account;

class OAuthUserProfileService
{
    /**
     * @return array<string, mixed>
     */
    public function buildResponseData(Account $account, int $clientId, bool $isImpersonating): array
    {
        $data = [];
        $data['cid'] = $account->id;
        $data['name_first'] = $account->name_first;
        $data['name_last'] = $account->name_last;
        $data['name_full'] = $account->name;
        $data['email'] = $this->getEmailForClient($account, $clientId);

        if ($account->qualifications_atc->isEmpty()) {
            $data['atc_rating'][] = 0;
            $data['atc_rating_human_short'][] = 'NA';
            $data['atc_rating_human_long'][] = 'None Awarded';
        } else {
            $data['atc_rating'] = $account->qualification_atc->vatsim;
            $data['atc_rating_human_short'] = $account->qualification_atc->name_small;
            $data['atc_rating_human_long'] = $account->qualification_atc->name_long;
            $data['atc_rating_date'] = $account->qualification_atc->pivot->created_at->toDateTimeString();
        }

        $pilotRatingsBin = 0;
        $pilotRatings = [];
        if ($account->qualifications_pilot->isEmpty()) {
            $pilotRatings[] = 0;
            $data['pilot_ratings_human_short'][] = 'NA';
            $data['pilot_ratings_human_long'][] = 'None Awarded';
        } else {
            foreach ($account->qualifications_pilot as $qual) {
                $entry = [
                    'rating' => $qual->vatsim,
                    'human_short' => $qual->name_small,
                    'human_long' => $qual->name_long,
                    'date' => $qual->pivot->created_at->toDateTimeString(),
                ];
                $pilotRatings[] = $entry;
                $pilotRatingsBin += $qual->vatsim;
            }
        }

        $data['pilot_ratings'] = $pilotRatings;
        $data['pilot_ratings_bin'] = decbin($pilotRatingsBin);
        $data['admin_ratings'] = $this->mapRatings($account->qualifications_admin);
        $data['training_pilot_ratings'] = $this->mapRatings($account->qualifications_pilot_training);
        $data['training_atc_ratings'] = $this->mapRatings($account->qualifications_atc_training);

        $data['account_state'] = $account->states;
        $data['account_state_current'] = $account->primary_state->name;
        $data['account_status'] = $account->status;
        $data['is_banned'] = (bool) $account->is_banned;
        $data['ban_info'] = $account->is_banned ? $account->bans->first() : null;
        $data['is_inactive'] = (bool) $account->is_inactive;
        $data['experience'] = $account->experience;
        $data['reg_date'] = $account->joined_at ? $account->joined_at->toDateTimeString() : $account->created_at;
        $data['impersonation'] = $isImpersonating;

        return $data;
    }

    private function getEmailForClient(Account $account, int $clientId): string
    {
        $email = $account->email;

        $ssoEmailAssigned = $account->ssoEmails->filter(function ($ssoemail) use ($clientId) {
            return $ssoemail->sso_account_id == $clientId;
        })->values();

        if ($ssoEmailAssigned && count($ssoEmailAssigned) > 0) {
            $email = $ssoEmailAssigned[0]->email->email;
        }

        return $email;
    }

    private function mapRatings(mixed $qualifications): array
    {
        $ratings = [];

        foreach ($qualifications as $qual) {
            $ratings[] = [
                'rating' => $qual->vatsim,
                'human_short' => $qual->name_small,
                'human_long' => $qual->name_long,
                'date' => $qual->pivot->created_at->toDateTimeString(),
            ];
        }

        return $ratings;
    }
}
