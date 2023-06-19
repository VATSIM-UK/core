<?php

namespace Vatsimuk\WaitingListsManager\Http;

use App\Events\Training\AccountChangedStatusInWaitingList;
use App\Models\Mship\Account;
use App\Models\Training\WaitingList;
use App\Models\Training\WaitingList\WaitingListAccount;
use App\Models\Training\WaitingList\WaitingListStatus;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Routing\Controller;

class WaitingListsManagerController extends Controller
{
    private $waitingList;

    public function __construct(WaitingList $waitingList)
    {
        $this->waitingList = $waitingList;
    }

    public function index(WaitingList $waitingList)
    {
        return $this->getWaitingListAccounts($waitingList, false);
    }

    public function activeIndex(WaitingList $waitingList)
    {
        return $this->getWaitingListAccounts($waitingList, true);
    }

    public function destroy(WaitingList $waitingList, Request $request)
    {
        $account = Account::findOrFail($request->get('account_id'));

        $waitingList->removeFromWaitingList($account);

        return [];
    }

    public function defer(WaitingList $waitingList, Request $request)
    {
        $account = Account::findOrFail($request->get('account_id'));

        $status = WaitingListStatus::find(WaitingListStatus::DEFERRED);

        $this->findWaitingListAccount($account, $waitingList)->addStatus($status);

        event(new AccountChangedStatusInWaitingList($account, $waitingList, $request->user()));

        return [];
    }

    public function active(WaitingList $waitingList, Request $request)
    {
        $account = Account::findOrFail($request->get('account_id'));

        $status = WaitingListStatus::find(WaitingListStatus::DEFAULT_STATUS);

        $this->findWaitingListAccount($account, $waitingList)->addStatus($status);

        event(new AccountChangedStatusInWaitingList($account, $waitingList, $request->user()));

        return [];
    }

    public function featureToggles(WaitingList $waitingList)
    {
        return response()->json($waitingList->feature_toggles_formatted);
    }

    private function findWaitingListAccount(Account &$account, WaitingList &$waitingList): WaitingListAccount
    {
        return $account->currentWaitingLists()
            ->where('training_waiting_list.id', $waitingList->id)
            ->first()
            ->pivot;
    }

    private function getWaitingListAccounts(WaitingList &$waitingList, bool $eligibility): AnonymousResourceCollection
    {
        return WaitingListAccountResource::collection($waitingList->load(['accounts', 'flags'])->accountsByEligibility($eligibility));
    }
}
