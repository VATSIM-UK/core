<?php

namespace App\Services\Mship;

use App\Models\Mship\Account;
use App\Models\Training\WaitingList;
use App\Models\Training\WaitingList\WaitingListRetentionCheck;
use App\Services\Mship\DTO\RetentionTokenResult;
use App\Services\Training\WaitingListRetentionChecks;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;

class WaitingListFlowService
{
    /**
     * @param  Collection<int, mixed>  $waitingListAccounts
     * @return array{atcWaitingListAccounts: Collection, pilotWaitingListAccounts: Collection}
     */
    public function splitWaitingListAccountsByDepartment(Collection $waitingListAccounts): array
    {
        $atcWaitingListAccounts = $waitingListAccounts->filter(function ($waitingListAccount) {
            return $waitingListAccount->waitingList->department == WaitingList::ATC_DEPARTMENT;
        })->values();

        $pilotWaitingListAccounts = $waitingListAccounts->filter(function ($waitingListAccount) {
            return $waitingListAccount->waitingList->department == WaitingList::PILOT_DEPARTMENT;
        })->values();

        return [
            'atcWaitingListAccounts' => $atcWaitingListAccounts,
            'pilotWaitingListAccounts' => $pilotWaitingListAccounts,
        ];
    }

    public function selfEnrol(WaitingList $waitingList, Account $actor): void
    {
        $waitingList->addToWaitingList($actor, $actor);
    }

    public function processRetentionToken(?string $token): RetentionTokenResult
    {
        if (! $token || empty($token)) {
            return new RetentionTokenResult('mship.waiting-lists.retention.fail', ['failReason' => 'No token provided']);
        }

        try {
            $retentionCheck = WaitingListRetentionCheck::where('token', $token)->firstOrFail();
        } catch (ModelNotFoundException) {
            return new RetentionTokenResult('mship.waiting-lists.retention.fail', ['failReason' => 'Invalid or expired token']);
        }

        if ($retentionCheck->status === WaitingListRetentionCheck::STATUS_USED || $retentionCheck->response_at !== null) {
            return new RetentionTokenResult('mship.waiting-lists.retention.success', ['extraMessage' => 'This retention check token has already been used, waiting list place has already been confirmed']);
        }

        if ($retentionCheck->status !== WaitingListRetentionCheck::STATUS_PENDING || $retentionCheck->expires_at < now()) {
            return new RetentionTokenResult('mship.waiting-lists.retention.fail', ['failReason' => 'Invalid or expired token']);
        }

        WaitingListRetentionChecks::markRetentionCheckAsUsed($retentionCheck);

        return new RetentionTokenResult('mship.waiting-lists.retention.success');
    }
}
