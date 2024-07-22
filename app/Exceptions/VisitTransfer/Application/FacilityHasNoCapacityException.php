<?php

namespace App\Exceptions\VisitTransferLegacy\Application;

use App\Models\VisitTransferLegacy\Facility;

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
