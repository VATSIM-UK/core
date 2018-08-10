<?php

namespace App\Http\Controllers\Adm\Training;

use App\Events\Training\AccountDemotedInWaitingList;
use App\Events\Training\AccountPromotedInWaitingList;
use App\Http\Controllers\Adm\AdmController;
use App\Http\Controllers\Adm\Mship\Account;
use App\Models\Training\WaitingList;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class WaitingListPositionController extends AdmController
{
    protected $waitingList;

    public function __construct(WaitingList $waitingList)
    {
        parent::__construct();

        $this->waitingList = $waitingList;
    }

    public function store(WaitingList $waitingList, Request $request)
    {
        $account = Account::findOrFail($request->get('account_id'));

        try {
            $waitingList->promote($account, $request->get('position'));
        } catch (ModelNotFoundException $e) {
        }

        event(new AccountDemotedInWaitingList($account, $waitingList));

        return Redirect::route(route('training.waitingList.show', $waitingList))
            ->withSuccess('Waiting list positions changed!');
    }

    public function update(WaitingList $waitingList, Request $request)
    {
        $account = Account::findOrFail($request->get('account_id'));

        try {
            $waitingList->promote($account, $request->get('position'));
        } catch (ModelNotFoundException $e) {
        }

        event(new AccountPromotedInWaitingList($account, $waitingList));

        return Redirect::route(route('training.waitingList.show', $waitingList))
            ->withSuccess('Waiting list positions changed.');
    }
}
