<?php

namespace Vatsimuk\WaitingListsManager\Http;

use App\Models\Training\WaitingList\WaitingListAccountFlag;
use Illuminate\Routing\Controller;

class WaitingListFlagController extends Controller
{
    public function toggle(WaitingListAccountFlag $flag)
    {
        if (! $flag->value) {
            $flag->mark();
        } else {
            $flag->unMark();
        }

        return response()->json([], 200);
    }
}
