<?php

namespace App\Services\Auth;

use App\Models\Mship\Account;
use App\Models\Mship\Account\Ban;

class LocalBanDisplayService
{
    public function canViewBanPage(Account $account): bool
    {
        return $account->is_system_banned;
    }

    public function getBanForDisplay(Account $account): ?Ban
    {
        if (! $this->canViewBanPage($account)) {
            return null;
        }

        return $account->system_ban->load('reason');
    }
}
