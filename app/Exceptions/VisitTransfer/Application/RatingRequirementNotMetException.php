<?php

namespace App\Exceptions\VisitTransfer\Application;

use App\Models\VisitTransfer\Facility;

class RatingRequirementNotMetException extends \Exception
{
    private $facility;

    public function __construct(Facility $facility)
    {
        $this->facility = $facility;

        $this->message = "Your current rating is outside the requirements for " . $this->facility->name . ".";
    }

    public function __toString()
    {
        return $this->message;
    }
}
