<?php

namespace App\Http\Controllers\Mship;

use App\Http\Controllers\BaseController;
use App\Models\Training\WaitingList;
use App\Services\Mship\WaitingListFlowService;
use App\Services\Training\WaitingListSelfEnrolment;
use Illuminate\Http\Request;

class WaitingLists extends BaseController
{
    public function __construct(private WaitingListFlowService $waitingListFlowService)
    {
        parent::__construct();
    }

    public function index(Request $request)
    {
        $groupedWaitingListAccounts = $this->waitingListFlowService->splitWaitingListAccountsByDepartment($request->user()->waitingListAccounts);

        $this->setTitle('Waiting Lists');

        return $this->viewMake('mship.waiting-lists.index')
            ->with('atcWaitingListAccounts', $groupedWaitingListAccounts['atcWaitingListAccounts'])
            ->with('atcSelfEnrolmentLists', WaitingListSelfEnrolment::getListsAccountCanSelfEnrol($request->user())->where('department', WaitingList::ATC_DEPARTMENT))
            ->with('pilotSelfEnrolmentLists', WaitingListSelfEnrolment::getListsAccountCanSelfEnrol($request->user())->where('department', WaitingList::PILOT_DEPARTMENT))
            ->with('pilotWaitingListAccounts', $groupedWaitingListAccounts['pilotWaitingListAccounts']);
    }

    public function selfEnrol(WaitingList $waitingList, Request $request)
    {
        $this->authorize('selfEnrol', $waitingList);

        if ($waitingList->isAtCapacity()) {
            abort(403, 'This waiting list is currently at capacity and is not accepting new enrolments.');
        }

        $this->waitingListFlowService->selfEnrol($waitingList, $request->user());

        return redirect()
            ->route('mship.waiting-lists.index')
            ->with('success', 'You have been added to the waiting list.');
    }

    public function getRetentionWithToken()
    {
        $result = $this->waitingListFlowService->processRetentionToken(request()->query('token'));

        return redirect()->route($result->route)->with($result->flash);
    }
}
