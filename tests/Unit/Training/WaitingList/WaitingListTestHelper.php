<?php

namespace Tests\Unit\Training\WaitingList;

use App\Models\Mship\Account;
use App\Models\Training\WaitingList;
use App\Models\Training\WaitingList\WaitingListStatus;

trait WaitingListTestHelper
{
    protected function createList(array $overrides = [])
    {
        return factory(WaitingList::class)->create($overrides);
    }

    protected function createPopulatedList(array $overrides = [], $accounts = 5)
    {
        $waitingList = $this->createList();

        $waitingListAccounts = factory(Account::class, $accounts)->create($overrides);

        $waitingListAccounts->each(function ($account) use ($waitingList) {
            $waitingList->addToWaitingList($account, $this->createAdminAccount());
        });

        return $waitingList->fresh();
    }

    protected function createStatus()
    {
        return factory(WaitingListStatus::class)->create();
    }

    private function createAdminAccount()
    {
        return factory(Account::class)->create();
    }
}
