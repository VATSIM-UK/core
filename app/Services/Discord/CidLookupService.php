<?php

declare(strict_types=1);

namespace App\Services\Discord;

use App\Models\Mship\Account;

final class CidLookupService
{
    public function lookup(int $cid): CidLookupResult
    {
        if ($cid <= 0) {
            return new CidLookupResult(CidLookupStatus::Invalid);
        }

        $account = Account::find($cid);

        if (! $account) {
            return new CidLookupResult(CidLookupStatus::NotFound);
        }

        if (! $account->discord_id) {
            return new CidLookupResult(CidLookupStatus::NotLinked);
        }

        return new CidLookupResult(CidLookupStatus::Found, $account->discord_id);
    }
}
