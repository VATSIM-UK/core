<?php

namespace App\Exceptions\Mship;

class DuplicateQualificationException extends \Exception
{
    private $qualification;

    public function __construct($qualification)
    {
        $this->qualification = $qualification;
    }

    public function __toString()
    {
        return 'Qualification  '.$this->qualification->name_grp.' already exists on this account.';
    }
}
