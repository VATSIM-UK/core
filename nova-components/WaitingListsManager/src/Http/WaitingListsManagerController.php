<?php

namespace Vatsimuk\WaitingListsManager\Http;

use App\Events\Training\AccountDemotedInWaitingList;
use App\Events\Training\AccountPromotedInWaitingList;
use App\Models\Mship\Account;
use App\Models\Training\WaitingList;
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
        return WaitingListAccountResource::collection($waitingList->accounts()->where('position', '>', 0)->orderBy('position')->get());
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
}
