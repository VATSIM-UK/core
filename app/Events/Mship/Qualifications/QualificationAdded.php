<?php

namespace App\Events\Mship\Qualifications;

use App\Events\Event;
use App\Models\Mship\Account;
use App\Models\Mship\Qualification;
use Illuminate\Queue\SerializesModels;

class QualificationAdded extends Event
{
    use SerializesModels;

    public $account;

    public $qualification;

    public function __construct(Account $account, Qualification $qualification)
    {
        $this->account = $account;
        $this->qualification = $qualification;
    }
}
