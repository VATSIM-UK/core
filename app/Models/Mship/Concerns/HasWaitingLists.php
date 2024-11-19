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
        return $this->waitingListAccounts()
            ->withTrashed()
            ->get()
            ->mapWithKeys(function (WaitingListAccount $waitingListAccount) {
                return [$waitingListAccount->waitingList->id => $waitingListAccount->waitingList];
            })
            ->values();
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

        // @fixme maybe replace with mapWithKeys
        $waitingLists = collect();
        foreach ($waitingListAccounts as $waitingListAccount) {
            $waitingList = $waitingListAccount->waitingList;
            $waitingLists->put($waitingList->id, $waitingList);
        }

        return $waitingLists->values();
    }
}
