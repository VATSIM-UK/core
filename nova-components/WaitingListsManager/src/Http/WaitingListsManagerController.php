<?php

namespace Vatsimuk\WaitingListsManager\Http;

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
}
