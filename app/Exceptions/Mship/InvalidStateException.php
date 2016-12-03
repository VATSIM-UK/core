<?php

namespace App\Exceptions\Mship;

use App\Models\Mship\State;

class InvalidStateException extends \Exception
{
    private $state;

    public function __construct(State $state = null)
    {
        $this->state = $state;
    }

    public function __toString()
    {
        return 'State is not valid.';
    }
}
