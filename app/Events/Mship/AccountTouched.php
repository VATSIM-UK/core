<?php

namespace App\Events\Mship;

use App\Events\Event;
use Illuminate\Queue\SerializesModels;
use App\Models\Mship\Account as AccountData;

class AccountTouched extends Event
{
    use SerializesModels;

    public $account;

    public function __construct(AccountData $account)
    {
        $this->account = $account;
    }
}
