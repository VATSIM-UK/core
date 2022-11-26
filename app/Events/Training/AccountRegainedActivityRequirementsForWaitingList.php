<?php

namespace App\Events\Training;

use App\Events\Event;
use App\Models\Mship\Account;
use App\Models\Training\WaitingList;
use Illuminate\Queue\SerializesModels;

class AccountRegainedActivityRequirementsForWaitingList extends Event
{
    use SerializesModels;

    public Account $account;
    public WaitingList $waitingList;

    public function __construct(Account $account, WaitingList $waitingList)
    {
        $this->account = $account;
        $this->waitingList = $waitingList;
    }
}
