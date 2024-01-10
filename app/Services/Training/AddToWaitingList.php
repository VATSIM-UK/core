<?php

namespace App\Services\Training;

use App\Models\Mship\Account;
use App\Models\Training\WaitingList;
use App\Services\BaseService;
use Carbon\Carbon;

class AddToWaitingList implements BaseService
{
    protected $waitingList;

    protected $staffAccount;

    protected $account;

    protected $createdAt;

    public function __construct(WaitingList $waitingList, Account $account, Account $staffAccount, ?Carbon $created_at = null)
    {
        $this->waitingList = $waitingList;
        $this->account = $account;
        $this->staffAccount = $staffAccount;
        $this->createdAt = $created_at;
    }

    public function handle()
    {
        $this->waitingList->addToWaitingList($this->account, $this->staffAccount, $this->createdAt);
    }
}
