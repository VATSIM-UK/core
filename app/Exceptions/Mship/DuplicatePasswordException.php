<?php

namespace App\Exceptions\Mship;

class DuplicatePasswordException extends \Exception
{
    public function __toString()
    {
        return 'It is not possible to update to the same password as already exists.';
    }
}
