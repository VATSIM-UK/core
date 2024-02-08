<?php

namespace App\Services\Training;

use App\Models\Training\WaitingList;

class WriteWaitingListEligibility
{
    public static function handle(WaitingList $waitingList, CheckWaitingListEligibility $service)
    {
        $waitingListAccount = $service->getWaitingListAccount($waitingList);

        $waitingListAccount->update([
            'flags_status_summary' => $service->checkWaitingListFlags($waitingList),
            'eligible' => $service->getOverallEligibility($waitingList),
            'eligibility_summary' => [
                'flags' => $service->checkWaitingListFlags($waitingList),
                'account_status' => $service->checkAccountStatus($waitingList),
            ],
        ]);
    }
}
