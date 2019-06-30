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

        $waitingListAccount->addNote($request->get('notes'));

        return response()->json(['success' => 'Note added!']);
    }

    public function edit(WaitingListAccount $waitingListAccount, Request $request)
    {
        $this->validate($request, [
            'notes' => 'required|string'
        ]);

        $waitingListAccount->editNote($request->get('notes'));

        return response()->json(['success' => 'Note modified!']);
    }
}
