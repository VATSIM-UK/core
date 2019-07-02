<?php

namespace App\Events\Training;

use App\Models\Mship\Account;
use App\Models\Training\WaitingListAccount;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;

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
     * @param Account $account
     * @param $oldNoteContent
     * @param $newNoteContent
     */
    public function __construct(WaitingListAccount $account, $oldNoteContent, $newNoteContent)
    {
        $this->waitingListAccount = $account;
        $this->account = $this->waitingListAccount->account;
        $this->oldNoteContent = $oldNoteContent;
        $this->newNoteContent = $newNoteContent;
    }
}
