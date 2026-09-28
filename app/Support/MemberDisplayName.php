<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Mship\Account;

final class MemberDisplayName
{
    /**
     * Preferred first name plus last initial, e.g. "Alex S.".
     *
     * Mirrors the bookings calendar, which never ships a full name to the
     * browser.
     */
    public static function abbreviated(Account $account): string
    {
        $first = (string) $account->name_preferred;
        $lastInitial = mb_substr((string) $account->name_last, 0, 1);

        return trim($first.' '.($lastInitial !== '' ? $lastInitial.'.' : ''));
    }

    /**
     * Abbreviated name plus the CID, e.g. "Alex S. (1234567)".
     */
    public static function abbreviatedWithCid(Account $account): string
    {
        return self::abbreviated($account)." ({$account->id})";
    }
}
