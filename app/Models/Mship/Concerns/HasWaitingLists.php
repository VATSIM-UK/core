<?php

namespace App\Models\Mship\Concerns;

use App\Models\Training\WaitingList;
use App\Models\Training\WaitingList\WaitingListAccount;
use Illuminate\Support\Collection;

trait HasWaitingLists
{
    public function waitingListAccounts()
    {
        return $this->hasMany(WaitingListAccount::class)->with('waitingList');
    }

    /**
     * Get *all* waiting lists for this account, including ones the account has been removed from
     *
     * @return Collection<WaitingList>
     */
    public function waitingLists()
    {
        // waiting list accounts soft delete so this will exclude them
        $waitingListAccounts = $this->waitingListAccounts()
            ->withTrashed()
            ->with('waitingList')
            ->get();

        return $this->extractListsFromWaitingListAccounts($waitingListAccounts);
    }

    /**
     * Get all "live" waiting lists for this account
     *
     * @return Collection<WaitingList>
     */
    public function currentWaitingLists(): Collection
    {
        // waiting list accounts soft delete so this will exclude them
        $waitingListAccounts = $this->waitingListAccounts()
            ->with('waitingList')
            ->get();

        return $this->extractListsFromWaitingListAccounts($waitingListAccounts);
    }

    /**
     * @param  Collection<WaitingListAccount>  $waitingListAccounts
     * @return Collection<WaitingList>
     */
    private function extractListsFromWaitingListAccounts(Collection $waitingListAccounts): Collection
    {
        $waitingLists = collect();
        foreach ($waitingListAccounts as $waitingListAccount) {
            $waitingList = $waitingListAccount->waitingList;

            if (empty($waitingList)) {
                // Waiting list has likely been soft deleted, we don't want to retrieve it here
                continue;
            }

            $waitingLists->put($waitingList->id, $waitingList);
        }

        return $waitingLists->values();
    }
}
