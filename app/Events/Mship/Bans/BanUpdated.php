<?php

namespace App\Events\Mship\Bans;

use App\Events\Event;
use App\Events\Mship\AccountAltered;
use App\Models\Mship\Account\Ban;
use Illuminate\Queue\SerializesModels;

class BanUpdated extends Event
{
    use SerializesModels;

    public $ban;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Ban $ban)
    {
        $this->ban = $ban;

        event(new AccountAltered($ban->account));
    }
}
