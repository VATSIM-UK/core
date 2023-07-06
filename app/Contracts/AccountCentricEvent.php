<?php

namespace App\Contracts;

use App\Models\Mship\Account;

interface AccountCentricEvent
{
    public function getAccount(): Account;
}
