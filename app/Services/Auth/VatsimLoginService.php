<?php

namespace App\Services\Auth;

use App\Models\Mship\Account;
use Carbon\Carbon;

class VatsimLoginService
{
    public function hasRequiredResourceOwnerFields(object $resourceOwner): bool
    {
        return $resourceOwner->data &&
            $resourceOwner->data->cid &&
            $resourceOwner->data->personal &&
            optional($resourceOwner->data->personal)->name_first &&
            optional($resourceOwner->data->personal)->name_last &&
            optional($resourceOwner->data->personal)->email &&
            $resourceOwner->data->vatsim &&
            $resourceOwner->data->oauth->token_valid === 'true';
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

        if ($resourceOwner->data->oauth->token_valid) {
            $account->vatsim_access_token = $token->getToken();
            $account->vatsim_refresh_token = $token->getRefreshToken();
            $account->vatsim_token_expires = $token->getExpires();
        }

        $account->save();

        return $account;
    }
}
