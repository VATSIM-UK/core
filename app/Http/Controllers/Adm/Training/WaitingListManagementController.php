<?php

namespace App\Http\Controllers\Adm\Training;

use App\Events\Training\AccountAddedToWaitingList;
use App\Events\Training\AccountRemovedFromWaitingList;
use App\Http\Controllers\Adm\AdmController;
use App\Http\Requests\Training\WaitingListAccountRequest;
use App\Models\Mship\Account;
use App\Models\Training\WaitingList;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;

class WaitingListManagementController extends AdmController
{
    protected $waitingList;

    public function __construct(WaitingList $waitingList)
    {
        parent::__construct();

        $this->waitingList = $waitingList;
    }

    public function index()
    {
        $lists = $this->waitingList::with(['accounts'])->get();

        return $this->viewMake('adm.training.index')->with('lists', $lists);
    }

    public function show(WaitingList $waitingList)
    {
        return $this->viewMake('adm.training.manage')
            ->with('waitingList', $waitingList->with([
                'accounts' => function ($query) {
                    $query->orderBy('position')->where('core.training_waiting_list_account.deleted_at', null);
                },
                'accounts.qualifications',
            ])->first());
    }

    public function store(WaitingList $waitingList, WaitingListAccountRequest $request)
    {
        $this->authorize('addAccount', $waitingList);

        try {
            $user = Account::findOrFail($request->get('account_id'));
        } catch (ModelNotFoundException $e) {
            return Redirect::route('training.waitingList.show', $waitingList)
                ->withError('Account Not Found.');
        }

        $waitingList->addToWaitingList($user, $request->user());

        event(new AccountAddedToWaitingList($user, $waitingList, $request->user()));

        return Redirect::route('training.waitingList.show', $waitingList)
            ->withSuccess('Account Added to Waiting List');
    }

    public function destroy(WaitingList $waitingList, Request $request)
    {
        $this->authorize('removeAccount', $waitingList);

        try {
            $user = Account::findOrFail($request->get('account_id'));
        } catch (ModelNotFoundException $e) {
            return Redirect::route('training.waitingList.show', $waitingList)
                ->withError('Account Not Found.');
        }

        $waitingList->removeFromWaitingList($user);

        event(new AccountRemovedFromWaitingList($user, $waitingList, $request->user()));

        return Redirect::route('training.waitingList.show', $waitingList)
            ->withSuccess('Student removed from Waiting List');
    }
}
