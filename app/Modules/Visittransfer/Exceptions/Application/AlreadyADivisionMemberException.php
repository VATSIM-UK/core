<?php

namespace App\Modules\Visittransfer\Exceptions\Application;

use App\Models\Mship\Account;

class AlreadyADivisionMemberException extends \Exception
{
    private $applicant;

    public function __construct(Account $applicant)
    {
        $this->applicant = $applicant;

        $this->message = 'It is not possible to create a visiting/transferring application for a division member.';
    }

    public function __toString()
    {
        return $this->message;
    }
}
