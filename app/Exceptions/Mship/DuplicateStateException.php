<?php

namespace App\Exceptions\Mship;

use App\Models\Mship\State;

class DuplicateStateException extends \Exception
{
    private $state;

    public function __construct(State $state)
    {
        $this->state = $state;
    }

    public function __toString()
    {
        return 'State '.$this->state->name.' already set on this account.';
    }
}
