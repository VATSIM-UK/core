<?php

namespace Vatsimuk\WaitingListsManager\Http;

use App\Events\Training\AccountChangedStatusInWaitingList;
use App\Events\Training\AccountDemotedInWaitingList;
use App\Events\Training\AccountPromotedInWaitingList;
use App\Models\Mship\Account;
use App\Models\Training\WaitingList;
use App\Models\Training\WaitingListStatus;
use Illuminate\Http\Request;
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
        return WaitingListAccountResource::collection(
            $waitingList->accounts()->where('position', '>', 0)->orderBy('position')->get()
                ->filter(function ($model, $key) {
                    return $model->pivot->eligibility == false;
                }));
    }

    public function activeIndex(WaitingList $waitingList)
    {
        return WaitingListAccountResource::collection(
            $waitingList->accounts()->where('position', '>', 0)->orderBy('position')->get()
                ->filter(function ($model, $key) {
                    return $model->pivot->eligibility == true;
                }));
    }

    public function destroy(WaitingList $waitingList, Request $request)
    {
        $account = Account::findOrFail($request->get('account_id'));

        $waitingList->removeFromWaitingList($account);

        return [];
    }

    public function promote(WaitingList $waitingList, Request $request)
    {
        $account = Account::findOrFail($request->get('account_id'));

        $waitingList->promote($account);

        event(new AccountPromotedInWaitingList($account, $waitingList, $request->user()));

        return [];
    }

    public function demote(WaitingList $waitingList, Request $request)
    {
        $account = Account::findOrFail($request->get('account_id'));

        $waitingList->demote($account);

        event(new AccountDemotedInWaitingList($account, $waitingList, $request->user()));

        return [];
    }

    public function defer(WaitingList $waitingList, Request $request)
    {
        $account = Account::findOrFail($request->get('account_id'));

        $status = WaitingListStatus::find(WaitingListStatus::DEFERRED);

        $account->waitingLists->where('pivot.position', '>', 0)->where('id', $waitingList->id)->first()->pivot->addStatus($status);

        event(new AccountChangedStatusInWaitingList($account, $waitingList, $request->user()));

        return [];
    }

    public function active(WaitingList $waitingList, Request $request)
    {
        $account = Account::findOrFail($request->get('account_id'));

        $status = WaitingListStatus::find(WaitingListStatus::DEFAULT_STATUS);

        $account->waitingLists->where('pivot.position', '>', 0)->where('id', $waitingList->id)->first()->pivot->addStatus($status);

        event(new AccountChangedStatusInWaitingList($account, $waitingList, $request->user()));

        return [];
    }

    private function findAccount($id)
    {
        return Account::findOrFail($id);
    }
}
