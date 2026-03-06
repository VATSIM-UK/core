<?php

namespace App\Services\Auth;

use App\Models\Mship\Account;
use Carbon\Carbon;

class VatsimLoginService
{
    public function hasRequiredResourceOwnerFields(object $resourceOwner): bool
    {
        return $this->hasAccountIdentityData($resourceOwner)
            && $this->hasPersonalData($resourceOwner)
            && $this->hasVatsimAndValidOauthToken($resourceOwner);
    }

    public function completeLogin(object $resourceOwner, object $token, string $ipAddress): Account
    {
        $account = Account::firstOrNew(['id' => $resourceOwner->data->cid]);
        $account->name_first = $resourceOwner->data->personal->name_first;
        $account->name_last = $resourceOwner->data->personal->name_last;
        $account->email = $resourceOwner->data->personal->email;
        $account->last_login = Carbon::now();
        $account->last_login_ip = $ipAddress;
        $account->is_inactive = null;
        $account->updateVatsimRatings($resourceOwner->data->vatsim->rating->id, $resourceOwner->data->vatsim->pilotrating->id);
        $account->updateDivision($resourceOwner->data->vatsim->division->id, $resourceOwner->data->vatsim->region->id);

        if ($this->isOauthTokenValid($resourceOwner)) {
            $this->setAccountTokens($account, $token);
        }

        $account->save();

        return $account;
    }

    private function hasAccountIdentityData(object $resourceOwner): bool
    {
        return isset($resourceOwner->data) && isset($resourceOwner->data->cid);
    }

    private function hasPersonalData(object $resourceOwner): bool
    {
        return isset($resourceOwner->data->personal)
            && filled(optional($resourceOwner->data->personal)->name_first)
            && filled(optional($resourceOwner->data->personal)->name_last)
            && filled(optional($resourceOwner->data->personal)->email);
    }

    private function hasVatsimAndValidOauthToken(object $resourceOwner): bool
    {
        return isset($resourceOwner->data->vatsim) && $resourceOwner->data->oauth->token_valid === 'true';
    }

    private function isOauthTokenValid(object $resourceOwner): bool
    {
        return (bool) $resourceOwner->data->oauth->token_valid;
    }

    private function setAccountTokens(Account $account, object $token): void
    {
        $account->vatsim_access_token = $token->getToken();
        $account->vatsim_refresh_token = $token->getRefreshToken();
        $account->vatsim_token_expires = $token->getExpires();
    }
}
