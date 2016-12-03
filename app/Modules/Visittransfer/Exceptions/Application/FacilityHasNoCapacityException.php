<?php

namespace App\Modules\Visittransfer\Exceptions\Application;

use App\Modules\Visittransfer\Models\Facility;

class FacilityHasNoCapacityException extends \Exception
{
    private $facility;

    public function __construct(Facility $facility)
    {
        $this->facility = $facility;

        $this->message = $this->facility->name." doesn't have enough training spaces for an application at this time.";
    }

    public function __toString()
    {
        return $this->message;
    }
}
