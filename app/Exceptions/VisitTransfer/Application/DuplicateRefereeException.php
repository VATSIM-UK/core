<?php

namespace App\Exceptions\VisitTransfer\Application;

use App\Models\Mship\Account;

class DuplicateRefereeException extends \Exception
{
    private $referee;

    public function __construct(Account $referee)
    {
        $this->referee = $referee;

        $this->message = $this->referee->name.' ('.$this->referee->account_id.') has already been added to this application.';
    }

    public function __toString()
    {
        return $this->message;
    }
}
