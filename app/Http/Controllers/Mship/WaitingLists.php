<?php

namespace App\Http\Controllers\Mship;

use App\Http\Controllers\BaseController;
use App\Models\Training\WaitingList;
use Illuminate\Http\Request;

class WaitingLists extends BaseController
{
    public function index(Request $request)
    {
        $atcWaitingLists = $request->user()->currentWaitingLists()
            ->withPivot([
                'created_at',
            ])->where('department', WaitingList::ATC_DEPARTMENT)->get();
        $pilotWaitingLists = $request->user()->currentWaitingLists()->withPivot([
            'created_at',
        ])->where('department', WaitingList::PILOT_DEPARTMENT)->get();

        return view('mship.waiting-lists.index', [
            'atcLists' => $atcWaitingLists,
            'pilotLists' => $pilotWaitingLists,
        ]);
    }

    public function view(Request $request, $waitingListId)
    {
        $list = $request->user()->currentWaitingLists()->where('training_waiting_list.id', $waitingListId)->withPivot([
            'created_at',
        ])->firstOrFail();

        $automaticFlags = $list->pivot->flags->filter(function ($flag) {
            return (bool) $flag->endorsement_id;
        });

        return view('mship.waiting-lists.view', ['list' => $list, 'automaticFlags' => $automaticFlags]);
    }
}
