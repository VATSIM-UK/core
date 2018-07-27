<?php

namespace App\Http\Controllers\Adm\Training;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Http\Controllers\Adm\AdmController;
use Illuminate\Support\Facades\Redirect;
use App\Models\Training\WaitingList;
use App\Models\Mship\Account;
use Illuminate\Http\Request;

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
                    $query->orderBy('position');
                },
            ])->first());
    }

    public function create(WaitingList $waitingList, Request $request)
    {
        return $this->viewMake('adm.training.create')
            ->with('waitingList', $waitingList);
    }

    public function store(WaitingList $waitingList, Request $request)
    {
        try {
            $user = Account::findOrFail($request->get('account_id'));
        } catch (ModelNotFoundException $e) {
            return Redirect::route('training.waitingList.show', $waitingList)
                ->withError('Account Not Found.');
        }

        $waitingList->addToWaitingList($user);

        return Redirect::route('training.waitingList.show', $waitingList)
            ->withSuccess('Account Added to Waiting List');
    }
}
