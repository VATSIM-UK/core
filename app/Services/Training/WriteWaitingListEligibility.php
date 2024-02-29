<?php

namespace App\Services\Training;

use App\Models\Training\WaitingList;

class WriteWaitingListEligibility
{
    public static function handle(WaitingList $waitingList, CheckWaitingListEligibility $service)
    {
        $waitingListAccount = $service->getWaitingListAccount($waitingList);

        $waitingListAccount->update([
            'eligible' => $service->getOverallEligibility($waitingList),
        ]);
    }
}
