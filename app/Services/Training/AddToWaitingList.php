<?php

namespace App\Services\Training;

use App\Events\Training\AccountAddedToWaitingList;
use App\Models\Mship\Account;
use App\Models\Training\WaitingList;
use App\Services\BaseService;

class AddToWaitingList implements BaseService
{
    protected $waitingList;
    protected $staffAccount;
    protected $account;

    public function __construct(WaitingList $waitingList, Account $account, Account $staffAccount)
    {
        $this->waitingList = $waitingList;
        $this->account = $account;
        $this->staffAccount = $staffAccount;
    }

    public function handle()
    {
        $this->waitingList->addToWaitingList($this->account, $this->staffAccount);

        $this->waitingList = $this->waitingList->fresh();

        event(new AccountAddedToWaitingList($this->account, $this->waitingList->fresh(), $this->staffAccount));
    }
}
