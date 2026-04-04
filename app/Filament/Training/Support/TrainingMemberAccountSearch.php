<?php

declare(strict_types=1);

namespace App\Filament\Training\Support;

use App\Models\Cts\Member;
use App\Models\Mship\Account;
use Illuminate\Support\Collection;

/**
 * CTS member search by name/CID, aligned with exam setup pilot student select.
 * Select keys are Core account IDs (VATSIM CID).
 */
final class TrainingMemberAccountSearch
{
    /**
     * @return Collection<int, Member>
     */
    public static function membersMatchingSearch(string $search, int $limit = 25): Collection
    {
        $search = trim($search);
        if ($search === '') {
            return collect();
        }

        return Member::query()
            ->where(fn ($q) => $q
                ->where('name', 'LIKE', '%'.$search.'%')
                ->orWhere('cid', 'LIKE', '%'.$search.'%'))
            ->limit($limit)
            ->get();
    }

    /**
     * @param  Collection<int, Member>  $members
     * @return array<int, string>
     */
    public static function accountSelectOptionsFromMembers(Collection $members): array
    {
        if ($members->isEmpty()) {
            return [];
        }

        $existingAccountIds = Account::query()
            ->whereIn('id', $members->pluck('cid'))
            ->pluck('id');

        return $members
            ->whereIn('cid', $existingAccountIds)
            ->mapWithKeys(fn (Member $member): array => [
                (int) $member->cid => $member->name.' ('.$member->cid.')',
            ])
            ->all();
    }

    /**
     * @return array<int, string>
     */
    public static function searchAccountsForSelect(string $search, int $limit = 50): array
    {
        return self::accountSelectOptionsFromMembers(
            self::membersMatchingSearch($search, $limit)
        );
    }
}
