<?php

namespace App\Http\Controllers\Adm\Training;

use App\Http\Controllers\Adm\AdmController;
use App\Models\Training\WaitingList;

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
}
