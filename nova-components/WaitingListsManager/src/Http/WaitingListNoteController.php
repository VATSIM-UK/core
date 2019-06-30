<?php

namespace Vatsimuk\WaitingListsManager\Http;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Models\Training\WaitingListAccount;
use Illuminate\Foundation\Validation\ValidatesRequests;

class WaitingListNoteController extends Controller
{
    use ValidatesRequests;

    public function create(WaitingListAccount $waitingListAccount, Request $request)
    {
        $this->validate($request, [
            'notes' => 'string|nullable'
        ]);

        $waitingListAccount->notes = $request->get('notes');
        // persist the changes
        $waitingListAccount->save();

        return response()->json(['success' => 'Note added!']);
    }
}
