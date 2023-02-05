<?php

namespace App\Events\Mship;

use App\Events\Event;
use App\Models\Mship\Account as AccountData;
use Illuminate\Queue\SerializesModels;

class AccountAltered extends Event
{
    use SerializesModels;

    public $account;
    // NB: Not implemented in all listeners but designed to diagnose issues
    public bool $dryRun;

    public function __construct(AccountData $account)
    {
        $this->account = $account;
        // enable dry run when debug is true
        $this->dryRun = config("app.debug_waiting_list_removals");
    }
}
