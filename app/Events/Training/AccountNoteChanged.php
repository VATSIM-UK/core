<?php

namespace App\Events\Training;

use App\Models\Mship\Account;
use App\Models\Training\WaitingList\WaitingListAccount;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AccountNoteChanged implements ShouldQueue
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $account;

    /** @var WaitingListAccount */
    public $waitingListAccount;

    public $oldNoteContent = null;

    public $newNoteContent;

    /**
     * Create a new event instance.
     *
     * @param  Account  $account
     */
    public function __construct(WaitingListAccount $account, $oldNoteContent, $newNoteContent)
    {
        $this->waitingListAccount = $account;
        $this->account = $this->waitingListAccount->account;
        $this->oldNoteContent = $oldNoteContent;
        $this->newNoteContent = $newNoteContent;
    }
}
