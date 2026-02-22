<?php

namespace App\Http\Controllers\Mship;

use App\Http\Controllers\BaseController;
use App\Models\Training\WaitingList;
use App\Services\Mship\WaitingListFlowService;
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

        $selfEnrolmentLists = $this->waitingListFlowService->splitSelfEnrolmentListsByDepartment($request->user());

        $this->setTitle('Waiting Lists');

        return $this->viewMake('mship.waiting-lists.index')
            ->with('atcWaitingListAccounts', $groupedWaitingListAccounts['atcWaitingListAccounts'])
            ->with('atcSelfEnrolmentLists', $selfEnrolmentLists['atcSelfEnrolmentLists'])
            ->with('pilotSelfEnrolmentLists', $selfEnrolmentLists['pilotSelfEnrolmentLists'])
            ->with('pilotWaitingListAccounts', $groupedWaitingListAccounts['pilotWaitingListAccounts']);
    }

    public function selfEnrol(WaitingList $waitingList, Request $request)
    {
        $this->authorize('selfEnrol', $waitingList);

        $selfEnrolResult = $this->waitingListFlowService->getSelfEnrolResult($waitingList);

        if (! $selfEnrolResult->allowed) {
            abort(403, (string) $selfEnrolResult->message);
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
