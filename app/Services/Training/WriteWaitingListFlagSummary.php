<?php

namespace App\Services\Training;

use App\Models\Training\WaitingList;

class WriteWaitingListFlagSummary
{
    public static function handle(WaitingList $waitingList, CheckWaitingListFlags $service): void
    {
        $waitingListAccount = $service->getWaitingListAccount($waitingList);

        $waitingListAccount->update([
            'flags_status_summary' => $service->checkWaitingListFlags($waitingList),
        ]);
    }
}
