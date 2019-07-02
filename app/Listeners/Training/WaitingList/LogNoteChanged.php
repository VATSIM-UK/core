<?php

namespace App\Listeners\Training\WaitingList;

use App\Events\Training\AccountNoteChanged;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class LogNoteChanged
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  AccountNoteChanged  $event
     * @return void
     */
    public function handle(AccountNoteChanged $event)
    {
        //
    }
}
