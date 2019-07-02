<?php

namespace App\Events\Training;

use App\Models\Mship\Account;
use Illuminate\Broadcasting\Channel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;

class AccountNoteChanged
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $account;
    public $oldNoteContent = null;
    public $newNoteContent;

    /**
     * Create a new event instance.
     *
     * @param Account $account
     * @param $oldNoteContent
     * @param $newNoteContent
     */
    public function __construct(Account $account, $oldNoteContent, $newNoteContent)
    {
        $this->account = $account;
        $this->oldNoteContent = $oldNoteContent;
        $this->newNoteContent = $newNoteContent;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
