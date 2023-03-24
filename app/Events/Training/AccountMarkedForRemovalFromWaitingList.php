<?php

namespace App\Events\Training;

use App\Events\Event;
use App\Models\Mship\Account;
use App\Models\Training\WaitingList;
use Carbon\Carbon;
use Illuminate\Queue\SerializesModels;

class AccountMarkedForRemovalFromWaitingList extends Event
{
    use SerializesModels;

    public Account $account;
    public WaitingList $waitingList;
    public Carbon $removalDate;

    public function __construct(Account $account, WaitingList $waitingList, Carbon $removalDate)
    {
        $this->account = $account;
        $this->waitingList = $waitingList;
        $this->removalDate = $removalDate;
    }
}
