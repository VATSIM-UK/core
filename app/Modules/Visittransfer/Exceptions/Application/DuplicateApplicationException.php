<?php

namespace App\Modules\Visittransfer\Exceptions\Application;

use App\Models\Mship\Account;

class DuplicateApplicationException extends \Exception
{
    private $applicant;

    public function __construct(Account $applicant)
    {
        $this->applicant = $applicant;

        $this->message = 'There is already an open application for '.$this->applicant->name.' ('.$this->applicant->account_id.').  Duplicate applications are not permitted.';
    }

    public function __toString()
    {
        return $this->message;
    }
}
