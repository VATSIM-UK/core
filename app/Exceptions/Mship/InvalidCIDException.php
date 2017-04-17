<?php

namespace App\Exceptions\Mship;

class InvalidCIDException extends \Exception
{
    private $state;

    public function __construct()
    {
        //
    }

    public function __toString()
    {
        return 'CID is invalid.';
    }
}
