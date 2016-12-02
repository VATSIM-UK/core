<?php

namespace App\Exceptions\Mship;

class DuplicateEmailException extends \Exception
{
    private $emailAddress;

    public function __construct($emailAddress)
    {
        $this->emailAddress = $emailAddress;
    }

    public function __toString()
    {
        return 'Email address '.$this->emailAddress.' was already in use for this account.';
    }
}
