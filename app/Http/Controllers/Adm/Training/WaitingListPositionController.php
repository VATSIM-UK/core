<?php

namespace App\Http\Controllers\Adm\Training;

use App\Events\Training\AccountDemotedInWaitingList;
use App\Events\Training\AccountPromotedInWaitingList;
use App\Http\Controllers\Adm\AdmController;
use App\Models\Mship\Account;
use App\Models\Training\WaitingList;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class WaitingListPositionController extends AdmController
{
    /**
     * Promote Account within WaitingList.
     *
     * @param WaitingList $waitingList
     * @param Request $request
     * @return mixed
     */
    public function store(WaitingList $waitingList, Request $request)
    {
        $this->authorize('promoteAccount', $waitingList);

        $account = Account::findOrFail($request->get('account_id'));

        try {
            $waitingList->promote($account, $request->get('position'));
        } catch (ModelNotFoundException $e) {
        }

        event(new AccountPromotedInWaitingList($account, $waitingList, $request->user()));

        return Redirect::route('training.waitingList.show', $waitingList)
            ->withSuccess('Waiting list positions changed.');
    }

    /**
     * Demote Account within WaitingList.
     *
     * @param WaitingList $waitingList
     * @param Request $request
     * @return mixed
     */
    public function update(WaitingList $waitingList, Request $request)
    {
        $this->authorize('demoteAccount', $waitingList);

        $account = Account::findOrFail($request->get('account_id'));

        try {
            $waitingList->demote($account, $request->get('position'));
        } catch (ModelNotFoundException $e) {
        }

        event(new AccountDemotedInWaitingList($account, $waitingList, $request->user()));

        return Redirect::route('training.waitingList.show', $waitingList)
            ->withSuccess('Waiting list positions changed.');
    }
}
