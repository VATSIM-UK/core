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
            'isOBS' => $request->user()->qualification_atc->is_o_b_s,
            'atcLists' => $atcWaitingLists,
            'pilotLists' => $pilotWaitingLists,
        ]);
    }
}
