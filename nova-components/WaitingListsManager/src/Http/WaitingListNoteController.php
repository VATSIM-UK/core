<?php

namespace Vatsimuk\WaitingListsManager\Http;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use App\Events\Training\AccountNoteChanged;
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

        $currentNoteContent = $waitingListAccount->notes;
        $newNoteContent = $request->get('notes');

        event(new AccountNoteChanged($waitingListAccount->account, $currentNoteContent, $newNoteContent));

        $waitingListAccount->notes = $newNoteContent;
        // persist the changes
        $waitingListAccount->save();

        return response()->json(['success' => 'Note added!']);
    }
}
