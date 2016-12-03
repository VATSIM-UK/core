<?php

namespace App\Modules\Visittransfer\Exceptions\Facility;

class DuplicateFacilityNameException extends \Exception
{
    private $name;

    public function __construct($name)
    {
        $this->name = $name;

        $this->message = 'The name '.$this->name.' is already in use for a facility.';
    }

    public function __toString()
    {
        return $this->message;
    }
}
