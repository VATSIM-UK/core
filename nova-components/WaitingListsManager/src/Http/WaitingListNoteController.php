<?php

namespace Vatsimuk\WaitingListsManager\Http;

use App\Models\Training\WaitingListAccount;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class WaitingListNoteController extends Controller
{
    use ValidatesRequests;

    public function create(WaitingListAccount $waitingListAccount, Request $request)
    {
        $this->validate($request, [
            'notes' => 'required|string'
        ]);

        $waitingListAccount->addNote($request->get('notes'));

        return response()->json(['success' => 'Note added!']);
    }
}
