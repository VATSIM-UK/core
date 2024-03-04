<?php

namespace App\Listeners\Training\WaitingList;

use App\Events\Training\AccountNoteChanged;
use Illuminate\Support\Facades\Log;

class LogNoteChanged
{
    /**
     * Handle the event.
     *
     * @return void
     */
    public function handle(AccountNoteChanged $event)
    {
        Log::channel('training')
            ->info("A note about {$event->account->name} ({$event->account->id}) in waiting list {$event->waitingListAccount->waitingList->name} ({$event->waitingListAccount->waitingList->id}) was changed from 
            {$event->oldNoteContent} to {$event->newNoteContent}");
    }
}
