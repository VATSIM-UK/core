<?php

namespace App\Http\Controllers\Mship;

use App\Http\Controllers\BaseController;
use App\Models\Training\WaitingList;
use App\Models\Training\WaitingList\WaitingListAccount;
use App\Models\Training\WaitingList\WaitingListRetentionCheck;
use App\Services\Training\WaitingListRetentionChecks;
use App\Services\Training\WaitingListSelfEnrolment;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class WaitingLists extends BaseController
{
    public function index(Request $request)
    {
        /** @var Collection<WaitingListAccount> $waitingListAccounts */
        $waitingListAccounts = $request->user()->waitingListAccounts;

        $atcWaitingListAccounts = collect();
        $pilotWaitingListAccounts = collect();

        foreach ($waitingListAccounts as $waitingListAccount) {
            if ($waitingListAccount->waitingList->department == WaitingList::ATC_DEPARTMENT) {
                $atcWaitingListAccounts->push($waitingListAccount);
            }

            if ($waitingListAccount->waitingList->department == WaitingList::PILOT_DEPARTMENT) {
                $pilotWaitingListAccounts->push($waitingListAccount);
            }
        }

        return view('mship.waiting-lists.index', [
            'atcWaitingListAccounts' => $atcWaitingListAccounts,
            'atcSelfEnrolmentLists' => WaitingListSelfEnrolment::getListsAccountCanSelfEnrol($request->user())->where('department', WaitingList::ATC_DEPARTMENT),
            'pilotSelfEnrolmentLists' => WaitingListSelfEnrolment::getListsAccountCanSelfEnrol($request->user())->where('department', WaitingList::PILOT_DEPARTMENT),
            'pilotWaitingListAccounts' => $pilotWaitingListAccounts,
        ]);
    }

    public function selfEnrol(WaitingList $waitingList, Request $request)
    {
        $this->authorize('selfEnrol', $waitingList);

        $waitingList->addToWaitingList($request->user(), $request->user());

        return redirect()
            ->route('mship.waiting-lists.index')
            ->with('success', 'You have been added to the waiting list.');
    }

    public function getRetentionWithToken()
    {
        $token = request()->query('token');

        if (! $token || empty($token)) {
            return redirect()
                ->route('mship.waiting-lists.retention.fail')
                ->with('failReason', 'No token provided');
        }

        try {
            $retentionCheck = WaitingListRetentionCheck::where('token', $token)->firstOrFail();
        } catch (ModelNotFoundException) {
            return redirect()
                ->route('mship.waiting-lists.retention.fail')
                ->with('failReason', 'Invalid or expired token');
        }

        // Only the scheduled command will change the status so we need to check the expires_at timestamp as well
        if ($retentionCheck->status === WaitingListRetentionCheck::STATUS_USED ||
            $retentionCheck->response_at !== null) {
            return redirect()
                ->route('mship.waiting-lists.retention.fail')
                ->with('failReason', 'This retention check token has already been used, waiting list place has already been confirmed');
        }

        if ($retentionCheck->status !== WaitingListRetentionCheck::STATUS_PENDING || $retentionCheck->expires_at < now()) {
            return redirect()
                ->route('mship.waiting-lists.retention.fail')
                ->with('failReason', 'Invalid or expired token');
        }

        WaitingListRetentionChecks::markRetentionCheckAsUsed($retentionCheck);

        return redirect()
            ->route('mship.waiting-lists.retention.success');
    }
}
