<?php

namespace App\Exceptions\Mship;

class InvalidCIDException extends \Exception
{
    public function __toString()
    {
        return 'CID is invalid.';
    }
}
