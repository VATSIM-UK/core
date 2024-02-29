<?php

namespace App\Services\Networkdata;

use App\Models\Mship\Account;
use App\Models\NetworkData\Atc;

class AtcNetworkdataService
{
    public static function getLatestNetworkdataForAccount(Account $account): ?Atc
    {
        return Atc::where(['account_id' => $account->id])->latest()->first();
    }
}
