<?php

namespace App\Services\Mship;

use App\Models\Mship\Account;
use App\Models\Training\WaitingList;
use App\Models\Training\WaitingList\WaitingListRetentionCheck;
use App\Services\Mship\DTO\RetentionTokenResult;
use App\Services\Mship\DTO\WaitingListSelfEnrolResult;
use App\Services\Training\WaitingListRetentionChecks;
use App\Services\Training\WaitingListSelfEnrolment;
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

    /**
     * @return array{atcSelfEnrolmentLists: Collection, pilotSelfEnrolmentLists: Collection}
     */
    public function splitSelfEnrolmentListsByDepartment(Account $account): array
    {
        $selfEnrolmentLists = WaitingListSelfEnrolment::getListsAccountCanSelfEnrol($account);

        return [
            'atcSelfEnrolmentLists' => $selfEnrolmentLists->where('department', WaitingList::ATC_DEPARTMENT),
            'pilotSelfEnrolmentLists' => $selfEnrolmentLists->where('department', WaitingList::PILOT_DEPARTMENT),
        ];
    }

    public function getSelfEnrolResult(WaitingList $waitingList): WaitingListSelfEnrolResult
    {
        if ($waitingList->isAtCapacity()) {
            return WaitingListSelfEnrolResult::denied('This waiting list is currently at capacity and is not accepting new enrolments.');
        }

        return WaitingListSelfEnrolResult::allowed();
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
