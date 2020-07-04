<?php

namespace App\Events\Cts;

use App\Models\Mship\Account;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StudentFailedSessionRequestCheck
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $account;
    public $rtsId;

    /**
     * Create a new event instance.
     *
     * @param  Account  $account
     * @param  int  $rtsId
     */
    public function __construct(Account $account, int $rtsId)
    {
        $this->account = $account;
        $this->rtsId = $rtsId;
    }
}
