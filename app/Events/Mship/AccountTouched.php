<?php

namespace App\Events\Mship;

use App\Events\Event;
use App\Models\Mship\Account as AccountData;
use Illuminate\Queue\SerializesModels;

class AccountTouched extends Event
{
    use SerializesModels;

    public $account;

    public function __construct(AccountData $account)
    {
        $this->account = $account;
    }
}
