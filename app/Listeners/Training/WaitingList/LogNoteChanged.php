<?php

namespace App\Listeners\Training\WaitingList;

use App\Events\Training\AccountNoteChanged;

class LogNoteChanged
{
    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(AccountNoteChanged $event)
    {
        audit('Waiting list note changed', [
            'account_id' => $event->account->id,
            'waiting_list_id' => $event->waitingListAccount->waitingList->id,
            'old_note' => $event->oldNoteContent,
            'new_note' => $event->newNoteContent,
        ]);
    }
}
